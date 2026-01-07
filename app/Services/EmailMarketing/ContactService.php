<?php

namespace App\Services\EmailMarketing;

use App\Models\Contact;
use App\Models\ContactList;
use App\Models\ContactTag;
use App\Models\ContactActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ContactService
{
    public function createContact(array $data, User $user): Contact
    {
        return DB::transaction(function () use ($data, $user) {
            $contact = Contact::create([
                'organization_id' => $user->primaryOrganization()->id,
                'email' => $data['email'],
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'custom_fields' => $data['custom_fields'] ?? [],
                'source' => $data['source'] ?? 'manual',
                'subscribed_at' => now(),
            ]);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->attachTags($contact, $data['tags']);
            }

            if (isset($data['contact_list_ids']) && is_array($data['contact_list_ids'])) {
                $contact->contactLists()->attach($data['contact_list_ids'], [
                    'subscribed_at' => now(),
                ]);
                $this->updateContactListCounts($data['contact_list_ids']);
            }

            $this->logActivity($contact, 'subscribed', 'Contact subscribed');

            return $contact->load(['tags', 'contactLists']);
        });
    }

    public function updateContact(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {
            $contact->update([
                'first_name' => $data['first_name'] ?? $contact->first_name,
                'last_name' => $data['last_name'] ?? $contact->last_name,
                'phone' => $data['phone'] ?? $contact->phone,
                'company' => $data['company'] ?? $contact->company,
                'job_title' => $data['job_title'] ?? $contact->job_title,
                'custom_fields' => $data['custom_fields'] ?? $contact->custom_fields,
            ]);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->syncTags($contact, $data['tags']);
            }

            if (isset($data['contact_list_ids']) && is_array($data['contact_list_ids'])) {
                $contact->contactLists()->sync($data['contact_list_ids']);
                $this->updateContactListCounts($data['contact_list_ids']);
            }

            $this->logActivity($contact, 'updated', 'Contact updated');

            return $contact->load(['tags', 'contactLists']);
        });
    }

    public function deleteContact(Contact $contact): bool
    {
        return DB::transaction(function () use ($contact) {
            $contactListIds = $contact->contactLists->pluck('id');
            $contact->tags()->delete();
            $contact->activities()->delete();
            $contact->contactLists()->detach();
            $contact->campaignRecipients()->delete();
            
            $deleted = $contact->delete();
            
            if ($deleted) {
                $this->updateContactListCounts($contactListIds->toArray());
            }

            return $deleted;
        });
    }

    public function importContactsFromFile(UploadedFile $file, User $user, array $options = []): array
    {
        $extension = $file->getClientOriginalExtension();
        $imported = 0;
        $skipped = 0;
        $errors = [];

        if ($extension === 'csv') {
            return $this->importFromCsv($file, $user, $options);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            return $this->importFromExcel($file, $user, $options);
        }

        throw new \Exception('Unsupported file format. Please upload CSV or Excel file.');
    }

    private function importFromCsv(UploadedFile $file, User $user, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $contactListIds = $options['contact_list_ids'] ?? [];
        $skipDuplicates = $options['skip_duplicates'] ?? true;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            
            if (empty($data['email'])) {
                $skipped++;
                continue;
            }

            $existingContact = Contact::where('organization_id', $user->primaryOrganization()->id)
                ->where('email', $data['email'])
                ->first();

            if ($existingContact && $skipDuplicates) {
                $skipped++;
                continue;
            }

            try {
                $contactData = [
                    'email' => $data['email'],
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'job_title' => $data['job_title'] ?? null,
                    'source' => $options['source'] ?? 'import',
                    'contact_list_ids' => $contactListIds,
                ];

                if ($existingContact) {
                    $this->updateContact($existingContact, $contactData);
                } else {
                    $this->createContact($contactData, $user);
                }

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$data['email']}: {$e->getMessage()}";
                $skipped++;
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function importFromExcel(UploadedFile $file, User $user, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $contactListIds = $options['contact_list_ids'] ?? [];
        $skipDuplicates = $options['skip_duplicates'] ?? true;

        try {
            $rows = Excel::toArray([], $file);
            
            if (empty($rows) || empty($rows[0])) {
                return [
                    'imported' => 0,
                    'skipped' => 0,
                    'errors' => ['Excel file is empty or invalid.'],
                ];
            }

            $dataRows = $rows[0];
            $headers = array_map('strtolower', array_map('trim', $dataRows[0] ?? []));
            
            if (empty($headers) || !in_array('email', $headers)) {
                return [
                    'imported' => 0,
                    'skipped' => 0,
                    'errors' => ['Excel file must contain an "email" column.'],
                ];
            }

            for ($i = 1; $i < count($dataRows); $i++) {
                $row = $dataRows[$i];
                $data = [];
                
                foreach ($headers as $index => $header) {
                    $data[$header] = $row[$index] ?? null;
                }
                
                if (empty($data['email'])) {
                    $skipped++;
                    continue;
                }

                $existingContact = Contact::where('organization_id', $user->primaryOrganization()->id)
                    ->where('email', $data['email'])
                    ->first();

                if ($existingContact && $skipDuplicates) {
                    $skipped++;
                    continue;
                }

                try {
                    $contactData = [
                        'email' => $data['email'],
                        'first_name' => $data['first_name'] ?? $data['firstname'] ?? null,
                        'last_name' => $data['last_name'] ?? $data['lastname'] ?? null,
                        'phone' => $data['phone'] ?? null,
                        'company' => $data['company'] ?? null,
                        'job_title' => $data['job_title'] ?? $data['jobtitle'] ?? null,
                        'source' => $options['source'] ?? 'import',
                        'contact_list_ids' => $contactListIds,
                    ];

                    if ($existingContact) {
                        $this->updateContact($existingContact, $contactData);
                    } else {
                        $this->createContact($contactData, $user);
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($i + 1) . " ({$data['email']}): {$e->getMessage()}";
                    $skipped++;
                }
            }

            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => array_merge($errors, ['Failed to parse Excel file: ' . $e->getMessage()]),
            ];
        }
    }

    public function findDuplicates(Contact $contact): \Illuminate\Database\Eloquent\Collection
    {
        return Contact::where('organization_id', $contact->organization_id)
            ->where('id', '!=', $contact->id)
            ->where(function ($query) use ($contact) {
                $query->where('email', $contact->email)
                    ->orWhere(function ($q) use ($contact) {
                        if ($contact->phone) {
                            $q->where('phone', $contact->phone);
                        }
                    });
            })
            ->get();
    }

    public function mergeContacts(Contact $primaryContact, array $contactIdsToMerge): Contact
    {
        return DB::transaction(function () use ($primaryContact, $contactIdsToMerge) {
            $contactsToMerge = Contact::whereIn('id', $contactIdsToMerge)
                ->where('organization_id', $primaryContact->organization_id)
                ->where('id', '!=', $primaryContact->id)
                ->get();

            foreach ($contactsToMerge as $contact) {
                if (!$primaryContact->first_name && $contact->first_name) {
                    $primaryContact->first_name = $contact->first_name;
                }
                if (!$primaryContact->last_name && $contact->last_name) {
                    $primaryContact->last_name = $contact->last_name;
                }
                if (!$primaryContact->phone && $contact->phone) {
                    $primaryContact->phone = $contact->phone;
                }
                if (!$primaryContact->company && $contact->company) {
                    $primaryContact->company = $contact->company;
                }

                $mergedCustomFields = array_merge(
                    $primaryContact->custom_fields ?? [],
                    $contact->custom_fields ?? []
                );
                $primaryContact->custom_fields = $mergedCustomFields;

                $contact->contactLists()->each(function ($list) use ($primaryContact) {
                    if (!$primaryContact->contactLists->contains($list->id)) {
                        $primaryContact->contactLists()->attach($list->id);
                    }
                });

                $contact->tags()->each(function ($tag) use ($primaryContact) {
                    if (!$primaryContact->tags()->where('tag', $tag->tag)->exists()) {
                        ContactTag::create([
                            'contact_id' => $primaryContact->id,
                            'tag' => $tag->tag,
                        ]);
                    }
                });

                $contact->delete();
            }

            $primaryContact->save();
            $this->logActivity($primaryContact, 'updated', 'Contacts merged');

            return $primaryContact->load(['tags', 'contactLists']);
        });
    }

    public function exportContactData(Contact $contact): array
    {
        return [
            'email' => $contact->email,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'phone' => $contact->phone,
            'company' => $contact->company,
            'job_title' => $contact->job_title,
            'custom_fields' => $contact->custom_fields,
            'tags' => $contact->tags->pluck('tag')->toArray(),
            'contact_lists' => $contact->contactLists->pluck('name')->toArray(),
            'activities' => $contact->activities()->orderBy('occurred_at', 'desc')->get()->map(function ($activity) {
                return [
                    'type' => $activity->type,
                    'description' => $activity->description,
                    'occurred_at' => $activity->occurred_at,
                ];
            }),
            'subscribed_at' => $contact->subscribed_at,
            'unsubscribed_at' => $contact->unsubscribed_at,
        ];
    }

    public function deleteContactData(Contact $contact): bool
    {
        return $this->deleteContact($contact);
    }

    private function attachTags(Contact $contact, array $tags): void
    {
        foreach ($tags as $tag) {
            if (!$contact->tags()->where('tag', $tag)->exists()) {
                ContactTag::create([
                    'contact_id' => $contact->id,
                    'tag' => $tag,
                ]);
            }
        }
    }

    private function syncTags(Contact $contact, array $tags): void
    {
        $contact->tags()->delete();
        foreach ($tags as $tag) {
            ContactTag::create([
                'contact_id' => $contact->id,
                'tag' => $tag,
            ]);
        }
    }

    private function updateContactListCounts(array $contactListIds): void
    {
        foreach ($contactListIds as $listId) {
            $list = ContactList::find($listId);
            if ($list) {
                $list->updateContactCount();
            }
        }
    }

    private function logActivity(Contact $contact, string $type, string $description, array $metadata = []): void
    {
        ContactActivity::create([
            'contact_id' => $contact->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}


