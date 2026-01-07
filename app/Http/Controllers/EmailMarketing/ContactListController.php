<?php

namespace App\Http\Controllers\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailMarketing\CreateContactListRequest;
use App\Http\Resources\EmailMarketing\ContactListResource;
use App\Models\ContactList;
use App\Services\EmailMarketing\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactListController extends Controller
{
    public function __construct(
        private ContactService $contactService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = ContactList::where('organization_id', $organizationId);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $lists = $query->with('contacts')->orderBy('created_at', 'desc')->paginate();

        return ContactListResource::collection($lists);
    }

    public function store(CreateContactListRequest $request): JsonResponse
    {
        $list = ContactList::create([
            'organization_id' => auth()->user()->primaryOrganization()->id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'data' => new ContactListResource($list),
            'message' => 'Contact list created successfully.',
        ], 201);
    }

    public function show(ContactList $contactList): JsonResponse
    {
        $contactList->load('contacts.tags');

        return response()->json([
            'success' => true,
            'data' => new ContactListResource($contactList),
        ]);
    }

    public function update(CreateContactListRequest $request, ContactList $contactList): JsonResponse
    {
        $contactList->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', $contactList->is_active),
        ]);

        return response()->json([
            'success' => true,
            'data' => new ContactListResource($contactList),
            'message' => 'Contact list updated successfully.',
        ]);
    }

    public function destroy(ContactList $contactList): JsonResponse
    {
        $contactList->contacts()->detach();
        $contactList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact list deleted successfully.',
        ]);
    }

    public function addContacts(Request $request, ContactList $contactList): JsonResponse
    {
        $request->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        $contactList->contacts()->syncWithoutDetaching($request->contact_ids);
        $contactList->updateContactCount();

        return response()->json([
            'success' => true,
            'message' => 'Contacts added to list successfully.',
        ]);
    }

    public function removeContacts(Request $request, ContactList $contactList): JsonResponse
    {
        $request->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        $contactList->contacts()->detach($request->contact_ids);
        $contactList->updateContactCount();

        return response()->json([
            'success' => true,
            'message' => 'Contacts removed from list successfully.',
        ]);
    }
}

