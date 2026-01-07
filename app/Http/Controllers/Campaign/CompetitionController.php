<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Competitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function index(Request $request, string $organizationId, Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);

        $competitors = Competitor::where('organization_id', $organizationId)
            ->with(['analyses', 'posts'])
            ->paginate();

        return response()->json([
            'success' => true,
            'data' => $competitors,
        ]);
    }

    public function attachCompetitor(Request $request, string $organizationId, Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        $validated = $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
        ]);

        $competitor = Competitor::findOrFail($validated['competitor_id']);

        if ($competitor->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Competitor does not belong to this organization.',
            ], 403);
        }

        $campaign->competitors()->syncWithoutDetaching([$competitor->id]);

        return response()->json([
            'success' => true,
            'message' => 'Competitor attached to campaign successfully.',
        ]);
    }

    public function detachCompetitor(string $organizationId, Campaign $campaign, Competitor $competitor): JsonResponse
    {
        $this->authorize('update', $campaign);

        $campaign->competitors()->detach($competitor->id);

        return response()->json([
            'success' => true,
            'message' => 'Competitor detached from campaign successfully.',
        ]);
    }
}


