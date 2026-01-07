<?php

namespace App\Http\Controllers\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailMarketing\CreateEmailTemplateRequest;
use App\Http\Resources\EmailMarketing\EmailTemplateResource;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmailTemplateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = EmailTemplate::where(function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId)
                ->orWhere('is_public', true);
        });

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate();

        return EmailTemplateResource::collection($templates);
    }

    public function store(CreateEmailTemplateRequest $request): JsonResponse
    {
        $template = EmailTemplate::create([
            'organization_id' => auth()->user()->primaryOrganization()->id,
            'name' => $request->name,
            'description' => $request->description,
            'subject' => $request->subject,
            'html_content' => $request->html_content,
            'text_content' => $request->text_content,
            'variables' => $request->variables ?? [],
            'category' => $request->category ?? 'custom',
            'is_public' => $request->boolean('is_public', false),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => new EmailTemplateResource($template),
            'message' => 'Email template created successfully.',
        ], 201);
    }

    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new EmailTemplateResource($emailTemplate),
        ]);
    }

    public function update(CreateEmailTemplateRequest $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $emailTemplate->update([
            'name' => $request->name,
            'description' => $request->description,
            'subject' => $request->subject,
            'html_content' => $request->html_content,
            'text_content' => $request->text_content,
            'variables' => $request->variables ?? $emailTemplate->variables,
            'category' => $request->category ?? $emailTemplate->category,
            'is_public' => $request->boolean('is_public', $emailTemplate->is_public),
        ]);

        return response()->json([
            'success' => true,
            'data' => new EmailTemplateResource($emailTemplate),
            'message' => 'Email template updated successfully.',
        ]);
    }

    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $emailTemplate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email template deleted successfully.',
        ]);
    }

    public function render(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $request->validate([
            'data' => ['required', 'array'],
        ]);

        $rendered = $emailTemplate->render($request->data);

        return response()->json([
            'success' => true,
            'data' => $rendered,
        ]);
    }
}

