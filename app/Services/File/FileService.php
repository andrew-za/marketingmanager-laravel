<?php

namespace App\Services\File;

use App\Models\ImageLibrary;
use App\Models\FileFolder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileService
{
    public function createFolder(string $organizationId, array $data, User $user): FileFolder
    {
        $path = $this->buildFolderPath($data['parent_id'] ?? null, $data['name']);

        return FileFolder::create([
            'organization_id' => $organizationId,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'path' => $path,
            'created_by' => $user->id,
        ]);
    }

    public function updateFolder(FileFolder $folder, array $data): FileFolder
    {
        if (isset($data['name']) && $data['name'] !== $folder->name) {
            $data['path'] = $this->buildFolderPath($folder->parent_id, $data['name']);
        }

        $folder->update($data);
        return $folder->fresh();
    }

    public function deleteFolder(FileFolder $folder): void
    {
        DB::transaction(function () use ($folder) {
            $folder->files()->update(['folder_id' => null]);
            $folder->children()->delete();
            $folder->delete();
        });
    }

    public function shareFile(ImageLibrary $file, array $userIds, array $permissions = ['view']): ImageLibrary
    {
        $sharedWith = array_unique(array_merge($file->shared_with ?? [], $userIds));
        
        $file->update([
            'is_shared' => true,
            'shared_with' => $sharedWith,
            'permissions' => array_merge($file->permissions ?? [], $permissions),
        ]);

        return $file->fresh();
    }

    public function unshareFile(ImageLibrary $file, array $userIds): ImageLibrary
    {
        $sharedWith = array_diff($file->shared_with ?? [], $userIds);
        
        $file->update([
            'is_shared' => !empty($sharedWith),
            'shared_with' => array_values($sharedWith),
        ]);

        return $file->fresh();
    }

    public function createVersion(ImageLibrary $file, $newFile): ImageLibrary
    {
        $latestVersion = ImageLibrary::where('parent_file_id', $file->parent_file_id ?? $file->id)
            ->max('version') ?? 0;

        return ImageLibrary::create([
            'organization_id' => $file->organization_id,
            'folder_id' => $file->folder_id,
            'parent_file_id' => $file->parent_file_id ?? $file->id,
            'version' => $latestVersion + 1,
            'name' => $file->name,
            'description' => $file->description,
            'file_path' => $newFile->store("organizations/{$file->organization_id}/files", 'public'),
            'file_url' => Storage::url($newFile->store("organizations/{$file->organization_id}/files", 'public')),
            'mime_type' => $newFile->getMimeType(),
            'file_size' => $newFile->getSize(),
            'tags' => $file->tags,
            'source' => $file->source,
            'storage_provider' => $file->storage_provider ?? 'local',
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function bulkDelete(array $fileIds, string $organizationId): int
    {
        $files = ImageLibrary::where('organization_id', $organizationId)
            ->whereIn('id', $fileIds)
            ->get();

        $deletedCount = 0;

        DB::transaction(function () use ($files, &$deletedCount) {
            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
                $file->delete();
                $deletedCount++;
            }
        });

        return $deletedCount;
    }

    public function bulkMove(array $fileIds, ?int $folderId, string $organizationId): int
    {
        return ImageLibrary::where('organization_id', $organizationId)
            ->whereIn('id', $fileIds)
            ->update(['folder_id' => $folderId]);
    }

    public function bulkShare(array $fileIds, array $userIds, string $organizationId, array $permissions = ['view']): int
    {
        $files = ImageLibrary::where('organization_id', $organizationId)
            ->whereIn('id', $fileIds)
            ->get();

        $updatedCount = 0;

        foreach ($files as $file) {
            $this->shareFile($file, $userIds, $permissions);
            $updatedCount++;
        }

        return $updatedCount;
    }

    private function buildFolderPath(?int $parentId, string $name): string
    {
        if ($parentId) {
            $parent = FileFolder::find($parentId);
            return ($parent->path ?? '') . '/' . Str::slug($name);
        }

        return '/' . Str::slug($name);
    }
}

