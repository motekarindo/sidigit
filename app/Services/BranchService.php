<?php

namespace App\Services;

use App\Models\Branch;
use App\Repositories\BranchRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BranchService
{
    protected BranchRepository $repository;

    public function __construct(BranchRepository $repository)
    {
        $this->repository = $repository;
    }

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function store(array $data): Branch
    {
        $data = $this->preparePayload($data);
        $branch = $this->repository->create($data);
        $this->syncMainBranch($branch, (bool) ($data['is_main'] ?? false));

        return $branch;
    }

    public function update(int $id, array $data): Branch
    {
        $branch = $this->repository->findOrFail($id);
        $data = $this->preparePayload($data, $branch);
        $branch = $this->repository->update($branch, $data);
        $this->syncMainBranch($branch, (bool) ($data['is_main'] ?? $branch->is_main));

        return $branch;
    }

    public function destroy(int $id): void
    {
        $branch = $this->repository->findOrFail($id);
        if ($branch->is_main) {
            throw new \RuntimeException('Cabang induk tidak bisa dihapus.');
        }

        $this->deleteMedia($branch);
        $this->repository->delete($branch);
    }

    public function destroyMany(array $ids): void
    {
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $branches = $this->repository->query()->whereIn('id', $ids)->get();
        $protectedIds = $branches->where('is_main', true)->pluck('id')->all();

        $deleteIds = array_values(array_diff($ids, $protectedIds));
        if (empty($deleteIds)) {
            throw new \RuntimeException('Cabang induk tidak bisa dihapus.');
        }

        $branches = $this->repository->query()->whereIn('id', $deleteIds)->get();
        foreach ($branches as $branch) {
            $this->deleteMedia($branch);
        }
        $this->repository->query()->whereIn('id', $deleteIds)->delete();
    }

    public function find(int $id): Branch
    {
        return $this->repository->findOrFail($id);
    }

    protected function syncMainBranch(Branch $branch, bool $isMain): void
    {
        if (!$isMain) {
            if (!$this->repository->query()->where('is_main', true)->exists()) {
                $this->repository->update($branch, ['is_main' => true]);
            }
            return;
        }

        $this->repository->query()
            ->where('id', '!=', $branch->id)
            ->where('is_main', true)
            ->update(['is_main' => false]);
    }

    protected function preparePayload(array $data, ?Branch $branch = null): array
    {
        $disk = 'public';

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $data['logo_path'] = $data['logo']->store('branch-logos', $disk);
            if ($branch && $branch->logo_path) {
                Storage::disk($disk)->delete($branch->logo_path);
            }
        }

        if (isset($data['qris']) && $data['qris'] instanceof UploadedFile) {
            $data['qris_path'] = $data['qris']->store('branch-qris', $disk);
            if ($branch && $branch->qris_path) {
                Storage::disk($disk)->delete($branch->qris_path);
            }
        }

        unset($data['logo'], $data['qris']);

        return $data;
    }

    protected function deleteMedia(Branch $branch): void
    {
        $disk = 'public';
        if ($branch->logo_path) {
            Storage::disk($disk)->delete($branch->logo_path);
        }
        if ($branch->qris_path) {
            Storage::disk($disk)->delete($branch->qris_path);
        }
    }
}
