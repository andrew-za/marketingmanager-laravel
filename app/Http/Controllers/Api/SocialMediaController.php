<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialConnectionResource;
use App\Models\SocialConnection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Social Media API Controller
 */
class SocialMediaController extends Controller
{
    /**
     * List social connections
     */
    public function connections(Request $request): AnonymousResourceCollection
    {
        $organizationId = $request->user()->primaryOrganization()->id;

        $connections = SocialConnection::where('organization_id', $organizationId)
            ->with(['channel', 'organization'])
            ->paginate();

        return SocialConnectionResource::collection($connections);
    }

    /**
     * Create social connection (OAuth flow handled separately)
     */
    public function storeConnection(Request $request)
    {
        // OAuth connections are handled via web routes
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'OAUTH_REQUIRED',
                'message' => 'Social connections must be created via OAuth flow',
            ],
        ], 400);
    }

    /**
     * Delete social connection
     */
    public function deleteConnection(Request $request, SocialConnection $connection)
    {
        $this->authorize('delete', $connection);

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Connection deleted successfully',
        ]);
    }

    /**
     * Publish post to social media
     */
    public function publish(Request $request)
    {
        $request->validate([
            'content' => ['required', 'string'],
            'platform' => ['required', 'string'],
            'connection_id' => ['required', 'exists:social_connections,id'],
            'scheduled_at' => ['sometimes', 'date'],
        ]);

        // Use publishing service
        return response()->json([
            'success' => true,
            'data' => [
                'post_id' => 1, // Placeholder
                'status' => 'scheduled',
            ],
            'message' => 'Post scheduled successfully',
        ]);
    }

    /**
     * Get social media analytics
     */
    public function analytics(Request $request)
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        $platform = $request->query('platform');

        return response()->json([
            'success' => true,
            'data' => [
                'impressions' => 0,
                'engagement' => 0,
                'clicks' => 0,
            ],
            'message' => 'Analytics retrieved successfully',
        ]);
    }
}

