<?php

namespace App\Services;

use App\Support\UploadPath;
use App\Support\UploadStorage;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerService
{
    public function paginateByBranch(
        int $branchId,
        string $folder = 'all',
        string $search = '',
        string $sortDirection = 'desc',
        int $perPage = 20,
        int $page = 1
    ): LengthAwarePaginator {
        $disk = UploadStorage::disk();
        $prefix = $this->buildPrefix($branchId, $folder);

        $files = collect();

        try {
            $files = collect(Storage::disk($disk)->allFiles($prefix));
        } catch (\Throwable $e) {
            $files = collect();
        }

        if (filled($search)) {
            $needle = Str::lower($search);
            $files = $files->filter(fn (string $path) => Str::contains(Str::lower($path), $needle));
        }

        $rows = $files
            ->map(fn (string $path) => $this->toFileRow($disk, $branchId, $path))
            ->filter()
            ->values()
            ->sortBy('last_modified', SORT_NUMERIC, Str::lower($sortDirection) !== 'asc')
            ->values();

        $perPage = max(10, min(100, $perPage));
        $page = max(1, $page);
        $total = $rows->count();
        $items = $rows->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function folderOptions(int $branchId): Collection
    {
        $disk = UploadStorage::disk();
        $prefix = UploadPath::branchPrefix($branchId);

        try {
            $files = collect(Storage::disk($disk)->allFiles($prefix));
        } catch (\Throwable $e) {
            $files = collect();
        }

        $detectedFolders = $files
            ->map(function (string $path) use ($prefix) {
                $relative = Str::after($path, $prefix);
                $directory = trim(dirname($relative), './');

                return $directory === '' ? null : $directory;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $defaults = collect([
            'branches/logos',
            'branches/qris',
            'employees/photos',
            'orders/attachments',
        ]);

        return $defaults
            ->merge($detectedFolders)
            ->unique()
            ->sort()
            ->values();
    }

    public function deleteByBranch(int $branchId, string $path): void
    {
        $path = ltrim($path, '/');
        $allowedPrefix = UploadPath::branchPrefix($branchId);

        if (! Str::startsWith($path, $allowedPrefix)) {
            throw new \RuntimeException('Akses file tidak valid untuk cabang aktif.');
        }

        foreach (UploadStorage::deletionDisks() as $disk) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function temporaryUrl(string $path, int $minutes = 10): ?string
    {
        $disk = UploadStorage::disk();
        $path = ltrim($path, '/');
        $driver = config("filesystems.disks.{$disk}.driver");

        try {
            $storage = Storage::disk($disk);

            return $driver === 's3'
                ? $storage->temporaryUrl($path, now()->addMinutes($minutes))
                : $storage->url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function buildPrefix(int $branchId, string $folder): string
    {
        $prefix = UploadPath::branchPrefix($branchId);

        if ($folder === '' || $folder === 'all') {
            return $prefix;
        }

        return $prefix . trim($folder, '/') . '/';
    }

    protected function toFileRow(string $disk, int $branchId, string $path): ?array
    {
        $path = ltrim($path, '/');
        $branchPrefix = UploadPath::branchPrefix($branchId);
        $relativePath = Str::after($path, $branchPrefix);
        $filename = basename($path);
        $directory = trim(dirname($relativePath), './');

        try {
            $size = (int) Storage::disk($disk)->size($path);
        } catch (\Throwable $e) {
            $size = 0;
        }

        try {
            $lastModified = (int) Storage::disk($disk)->lastModified($path);
        } catch (\Throwable $e) {
            $lastModified = 0;
        }

        try {
            $mime = (string) (Storage::disk($disk)->mimeType($path) ?? 'application/octet-stream');
        } catch (\Throwable $e) {
            $mime = 'application/octet-stream';
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $isImage = Str::startsWith($mime, 'image/')
            || in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);

        $url = $this->temporaryUrl($path, 15);

        return [
            'path' => $path,
            'filename' => $filename,
            'directory' => $directory === '' ? '-' : $directory,
            'mime' => $mime,
            'extension' => $extension ?: '-',
            'size' => $size,
            'last_modified' => $lastModified,
            'updated_at' => $lastModified > 0 ? Carbon::createFromTimestamp($lastModified) : null,
            'is_image' => $isImage,
            'url' => $url,
            'preview_url' => $isImage ? $url : null,
        ];
    }
}
