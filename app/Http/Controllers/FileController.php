<?php

namespace App\Http\Controllers;

use App\Models\ImageLibrary;
use App\Models\FileFolder;
use App\Models\Brand;
use App\Models\Organization;
use App\Services\File\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * File Controller
 * Handles file management operations for brand-scoped files
 * Only shown when brand is selected
 */
class FileController extends Controller
{
    public function __construct(
        private FileService $fileService
    ) {}
    /**
     * Display file browser interface
     */
    public function index(Request $request, string $organizationId): \Illuminate\View\View
    {
        $brandId = $request->query('brandId');
        
        if (!$brandId) {
            abort(404, 'Brand must be selected to access files');
        }

        $organization = Organization::findOrFail($organizationId);
        $brand = Brand::where('id', $brandId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        $query = ImageLibrary::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc');

        // Filter by search term
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by file type
        if ($request->has('type')) {
            $type = $request->get('type');
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'document') {
                $query->where(function($q) {
                    $q->where('mime_type', 'like', 'application/pdf')
                      ->orWhere('mime_type', 'like', 'application/msword')
                      ->orWhere('mime_type', 'like', 'application/vnd.openxmlformats-officedocument%');
                });
            }
        }

        $files = $query->with('uploadedBy')->paginate(24);

        return view('files.index', [
            'organization' => $organization,
            'brand' => $brand,
            'files' => $files,
        ]);
    }

    /**
     * Store uploaded file
     */
    public function store(Request $request, string $organizationId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'brandId' => 'required|exists:brands,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
        ]);

        $brandId = $request->input('brandId');
        $brand = Brand::where('id', $brandId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        $file = $request->file('file');
        $path = $file->store("organizations/{$organizationId}/files", 'public');

        $imageLibrary = ImageLibrary::create([
            'organization_id' => $organizationId,
            'name' => $request->input('name') ?? $file->getClientOriginalName(),
            'description' => $request->input('description'),
            'file_path' => $path,
            'file_url' => Storage::url($path),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'tags' => $request->input('tags', []),
            'source' => 'upload',
            'uploaded_by' => $request->user()->id,
        ]);

        // Get image dimensions if it's an image
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo) {
                $imageLibrary->update([
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => $imageLibrary->load('uploadedBy'),
        ]);
    }

    /**
     * Display file preview
     */
    public function show(Request $request, string $organizationId, ImageLibrary $file): \Illuminate\View\View
    {
        if ($file->organization_id != $organizationId) {
            abort(404);
        }

        return view('files.show', [
            'organization' => Organization::findOrFail($organizationId),
            'file' => $file->load('uploadedBy'),
        ]);
    }

    /**
     * Delete file
     */
    public function destroy(Request $request, string $organizationId, ImageLibrary $file): JsonResponse
    {
        if ($file->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        DB::transaction(function () use ($file) {
            // Delete file from storage
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            // Delete record
            $file->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Download file
     */
    public function download(Request $request, string $organizationId, ImageLibrary $file)
    {
        if ($file->organization_id != $organizationId) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($file->file_path, $file->name);
    }

    public function createFolder(Request $request, string $organizationId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:file_folders,id',
        ]);

        $folder = $this->fileService->createFolder(
            $organizationId,
            $request->only(['name', 'description', 'parent_id']),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => $folder,
            'message' => 'Folder created successfully',
        ], 201);
    }

    public function shareFile(Request $request, string $organizationId, ImageLibrary $file): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'permissions' => 'nullable|array',
        ]);

        $file = $this->fileService->shareFile(
            $file,
            $request->user_ids,
            $request->permissions ?? ['view']
        );

        return response()->json([
            'success' => true,
            'data' => $file,
            'message' => 'File shared successfully',
        ]);
    }

    public function createVersion(Request $request, string $organizationId, ImageLibrary $file): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $version = $this->fileService->createVersion($file, $request->file('file'));

        return response()->json([
            'success' => true,
            'data' => $version,
            'message' => 'File version created successfully',
        ], 201);
    }

    public function bulkDelete(Request $request, string $organizationId): JsonResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:image_library,id',
        ]);

        $deletedCount = $this->fileService->bulkDelete(
            $request->file_ids,
            $organizationId
        );

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} files deleted successfully",
            'deleted_count' => $deletedCount,
        ]);
    }

    public function bulkMove(Request $request, string $organizationId): JsonResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:image_library,id',
            'folder_id' => 'nullable|exists:file_folders,id',
        ]);

        $movedCount = $this->fileService->bulkMove(
            $request->file_ids,
            $request->folder_id,
            $organizationId
        );

        return response()->json([
            'success' => true,
            'message' => "{$movedCount} files moved successfully",
            'moved_count' => $movedCount,
        ]);
    }

    public function bulkShare(Request $request, string $organizationId): JsonResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:image_library,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'permissions' => 'nullable|array',
        ]);

        $sharedCount = $this->fileService->bulkShare(
            $request->file_ids,
            $request->user_ids,
            $organizationId,
            $request->permissions ?? ['view']
        );

        return response()->json([
            'success' => true,
            'message' => "{$sharedCount} files shared successfully",
            'shared_count' => $sharedCount,
        ]);
    }
}

