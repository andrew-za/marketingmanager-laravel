<?php

namespace App\Http\Controllers\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailMarketing\CreateContactRequest;
use App\Http\Requests\EmailMarketing\UpdateContactRequest;
use App\Http\Requests\EmailMarketing\ImportContactsRequest;
use App\Http\Resources\EmailMarketing\ContactResource;
use App\Models\Contact;
use App\Services\EmailMarketing\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = Contact::where('organization_id', $organizationId)
            ->with(['tags', 'contactLists']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('contact_list_id')) {
            $query->whereHas('contactLists', function ($q) use ($request) {
                $q->where('contact_lists.id', $request->contact_list_id);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate();

        return ContactResource::collection($contacts);
    }

    public function store(CreateContactRequest $request): JsonResponse
    {
        $contact = $this->contactService->createContact(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
            'message' => 'Contact created successfully.',
        ], 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load(['tags', 'contactLists', 'activities.emailCampaign']);

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $contact = $this->contactService->updateContact(
            $contact,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
            'message' => 'Contact updated successfully.',
        ]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->contactService->deleteContact($contact);

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully.',
        ]);
    }

    public function import(ImportContactsRequest $request): JsonResponse
    {
        try {
            $result = $this->contactService->importContactsFromFile(
                $request->file('file'),
                $request->user(),
                [
                    'contact_list_ids' => $request->contact_list_ids ?? [],
                    'skip_duplicates' => $request->boolean('skip_duplicates', true),
                    'source' => $request->source ?? 'import',
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => "Imported {$result['imported']} contacts successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function findDuplicates(Contact $contact): JsonResponse
    {
        $duplicates = $this->contactService->findDuplicates($contact);

        return response()->json([
            'success' => true,
            'data' => ContactResource::collection($duplicates),
        ]);
    }

    public function merge(Request $request, Contact $contact): JsonResponse
    {
        $request->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        try {
            $mergedContact = $this->contactService->mergeContacts(
                $contact,
                $request->contact_ids
            );

            return response()->json([
                'success' => true,
                'data' => new ContactResource($mergedContact),
                'message' => 'Contacts merged successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function subscribe(Contact $contact): JsonResponse
    {
        $contact->subscribe();

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
            'message' => 'Contact subscribed successfully.',
        ]);
    }

    public function unsubscribe(Contact $contact): JsonResponse
    {
        $contact->unsubscribe();

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
            'message' => 'Contact unsubscribed successfully.',
        ]);
    }

    public function exportData(Contact $contact): JsonResponse
    {
        $data = $this->contactService->exportContactData($contact);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function deleteData(Contact $contact): JsonResponse
    {
        $this->contactService->deleteContactData($contact);

        return response()->json([
            'success' => true,
            'message' => 'Contact data deleted successfully.',
        ]);
    }
}

