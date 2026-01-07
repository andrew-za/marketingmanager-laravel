<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class ImageLibrary extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'image_library';

    protected $fillable = [
        'organization_id',
        'folder_id',
        'parent_file_id',
        'version',
        'name',
        'description',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'width',
        'height',
        'tags',
        'source',
        'storage_provider',
        'storage_path',
        'is_shared',
        'shared_with',
        'permissions',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'version' => 'integer',
            'is_shared' => 'boolean',
            'shared_with' => 'array',
            'permissions' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class);
    }

    public function parentFile(): BelongsTo
    {
        return $this->belongsTo(ImageLibrary::class, 'parent_file_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ImageLibrary::class, 'parent_file_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}


