<?php

namespace App\Http\Controllers\PressRelease;

use App\Http\Controllers\Controller;
use App\Http\Requests\PressRelease\CreatePressContactRequest;
use App\Http\Requests\PressRelease\UpdatePressContactRequest;
use App\Http\Resources\PressRelease\PressContactResource;
use App\Models\PressContact;
use App\Services\PressRelease\PressContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PressContactController extends Controller
{
    public function __construct(
        private PressContactService $pressContactService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = PressContact::where('organization_id', $organizationId);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $contacts = $query->orderBy('name')->paginate();

        return PressContactResource::collection($contacts);
    }

    public function store(CreatePressContactRequest $request): JsonResponse
    {
        $contact = $this->pressContactService->createContact(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new PressContactResource($contact),
            'message' => 'Press contact created successfully.',
        ], 201);
    }

    public function show(PressContact $pressContact): JsonResponse
    {
        $this->authorize('view', $pressContact);

        return response()->json([
            'success' => true,
            'data' => new PressContactResource($pressContact),
        ]);
    }

    public function update(UpdatePressContactRequest $request, PressContact $pressContact): JsonResponse
    {
        $this->authorize('update', $pressContact);

        $pressContact = $this->pressContactService->updateContact(
            $pressContact,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new PressContactResource($pressContact),
            'message' => 'Press contact updated successfully.',
        ]);
    }

    public function destroy(PressContact $pressContact): JsonResponse
    {
        $this->authorize('delete', $pressContact);

        $this->pressContactService->deleteContact($pressContact);

        return response()->json([
            'success' => true,
            'message' => 'Press contact deleted successfully.',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.name' => ['required', 'string'],
            'contacts.*.email' => ['required', 'email'],
        ]);

        $imported = $this->pressContactService->importContacts(
            $request->contacts,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$imported} contacts.",
            'imported_count' => $imported,
        ]);
    }
}

