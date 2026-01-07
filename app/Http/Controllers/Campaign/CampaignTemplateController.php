<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\CampaignTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CampaignTemplate::query();

        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        $templates = $query->with(['creator'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:social_media,email,content,paid_ads,press_release,general',
            'template_data' => 'required|array',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $template = CampaignTemplate::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $template->load(['creator']),
            'message' => 'Campaign template created successfully.',
        ], 201);
    }

    public function show(CampaignTemplate $template): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $template->load(['creator']),
        ]);
    }

    public function update(Request $request, CampaignTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|required|in:social_media,email,content,paid_ads,press_release,general',
            'template_data' => 'sometimes|required|array',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'data' => $template->load(['creator']),
            'message' => 'Campaign template updated successfully.',
        ]);
    }

    public function destroy(CampaignTemplate $template): JsonResponse
    {
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaign template deleted successfully.',
        ]);
    }

    public function createCampaignFromTemplate(Request $request, CampaignTemplate $template, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $campaign = $template->createCampaignFromTemplate(
            $request->user(),
            (int) $organizationId,
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $campaign,
            'message' => 'Campaign created from template successfully.',
        ], 201);
    }
}


