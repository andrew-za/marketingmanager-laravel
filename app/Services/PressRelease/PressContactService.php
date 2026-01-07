<?php

namespace App\Services\PressRelease;

use App\Models\PressContact;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PressContactService
{
    public function createContact(array $data, User $user): PressContact
    {
        return DB::transaction(function () use ($data, $user) {
            return PressContact::create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
            ]);
        });
    }

    public function updateContact(PressContact $contact, array $data): PressContact
    {
        return DB::transaction(function () use ($contact, $data) {
            $contact->update($data);
            return $contact->fresh();
        });
    }

    public function deleteContact(PressContact $contact): bool
    {
        return DB::transaction(function () use ($contact) {
            return $contact->delete();
        });
    }

    public function importContacts(array $contacts, User $user): int
    {
        $organizationId = $user->primaryOrganization()->id;
        $imported = 0;

        DB::transaction(function () use ($contacts, $organizationId, &$imported) {
            foreach ($contacts as $contactData) {
                PressContact::create([
                    'organization_id' => $organizationId,
                    'name' => $contactData['name'],
                    'email' => $contactData['email'],
                    'phone' => $contactData['phone'] ?? null,
                    'company' => $contactData['company'] ?? null,
                    'job_title' => $contactData['job_title'] ?? null,
                    'media_outlet' => $contactData['media_outlet'] ?? null,
                    'type' => $contactData['type'] ?? 'journalist',
                    'notes' => $contactData['notes'] ?? null,
                    'tags' => $contactData['tags'] ?? [],
                    'is_active' => $contactData['is_active'] ?? true,
                ]);
                $imported++;
            }
        });

        return $imported;
    }
}

