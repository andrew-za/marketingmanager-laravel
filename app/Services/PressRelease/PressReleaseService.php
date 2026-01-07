<?php

namespace App\Services\PressRelease;

use App\Models\PressRelease;
use App\Models\PressReleaseTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PressReleaseService
{
    public function createPressRelease(array $data, User $user): PressRelease
    {
        return DB::transaction(function () use ($data, $user) {
            return PressRelease::create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
                'created_by' => $user->id,
            ]);
        });
    }

    public function updatePressRelease(PressRelease $pressRelease, array $data): PressRelease
    {
        return DB::transaction(function () use ($pressRelease, $data) {
            $pressRelease->update($data);
            return $pressRelease->fresh();
        });
    }

    public function deletePressRelease(PressRelease $pressRelease): bool
    {
        return DB::transaction(function () use ($pressRelease) {
            return $pressRelease->delete();
        });
    }

    public function schedulePressRelease(PressRelease $pressRelease, \DateTime $releaseDate): PressRelease
    {
        return DB::transaction(function () use ($pressRelease, $releaseDate) {
            $pressRelease->update([
                'release_date' => $releaseDate,
                'status' => 'pending_review',
            ]);
            return $pressRelease->fresh();
        });
    }

    public function approvePressRelease(PressRelease $pressRelease): PressRelease
    {
        return DB::transaction(function () use ($pressRelease) {
            $pressRelease->update(['status' => 'approved']);
            return $pressRelease->fresh();
        });
    }

    public function distributePressRelease(PressRelease $pressRelease, array $contactIds): void
    {
        DB::transaction(function () use ($pressRelease, $contactIds) {
            foreach ($contactIds as $contactId) {
                $pressRelease->distributions()->create([
                    'press_contact_id' => $contactId,
                    'status' => 'pending',
                ]);
            }
            
            $pressRelease->update(['status' => 'distributed']);
        });
    }

    public function createTemplate(array $data, ?User $user = null): PressReleaseTemplate
    {
        return PressReleaseTemplate::create([
            'organization_id' => $user?->primaryOrganization()?->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'template_content' => $data['template_content'],
            'variables' => $data['variables'] ?? [],
            'metadata' => $data['metadata'] ?? [],
            'is_public' => $data['is_public'] ?? false,
            'created_by' => $user?->id,
        ]);
    }

    public function generateFromTemplate(PressReleaseTemplate $template, array $variables, User $user): PressRelease
    {
        $content = $template->template_content;
        
        foreach ($variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $this->createPressRelease([
            'title' => $variables['title'] ?? 'Untitled Press Release',
            'content' => $content,
            'status' => 'draft',
        ], $user);
    }
}

