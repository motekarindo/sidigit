<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $encodedPath = (string) $request->query('path', '');
        $decodedPath = base64_decode($encodedPath, true);
        abort_unless(is_string($decodedPath) && $decodedPath !== '', 404);

        $path = ltrim($decodedPath, '/');
        abort_unless(preg_match('/^\d+\//', $path) === 1, 403);

        $branchId = (int) Str::before($path, '/');
        abort_unless($this->allowedBranchIds($user)->contains($branchId), 403);

        $disk = collect(UploadStorage::deletionDisks())
            ->first(fn (string $candidateDisk) => Storage::disk($candidateDisk)->exists($path));
        abort_unless(filled($disk), 404);

        return Storage::disk($disk)->download($path, basename($path));
    }

    protected function allowedBranchIds($user): Collection
    {
        if ($user->hasRoleSlug('superadmin')) {
            return Branch::query()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        return collect([$user->branch_id])
            ->merge($user->branches()->pluck('branches.id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }
}
