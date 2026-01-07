<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use App\Jobs\PublishScheduledPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublishingController extends Controller
{
    public function publishNow(Request $request, ScheduledPost $scheduledPost)
    {
        $this->authorize('update', $scheduledPost);

        $validated = $request->validate([
            'connection_id' => 'required|exists:social_connections,id',
        ]);

        $connection = SocialConnection::findOrFail($validated['connection_id']);

        if (!$connection->isConnected()) {
            return response()->json([
                'message' => 'Connection is not active or expired',
            ], 400);
        }

        PublishScheduledPost::dispatch($scheduledPost, $connection);

        return response()->json([
            'message' => 'Post queued for publishing',
        ]);
    }

    public function publishToMultiple(Request $request, ScheduledPost $scheduledPost)
    {
        $this->authorize('update', $scheduledPost);

        $validated = $request->validate([
            'connection_ids' => 'required|array',
            'connection_ids.*' => 'exists:social_connections,id',
        ]);

        $connections = SocialConnection::whereIn('id', $validated['connection_ids'])
            ->where('organization_id', $scheduledPost->organization_id)
            ->get();

        $queued = 0;
        foreach ($connections as $connection) {
            if ($connection->isConnected()) {
                PublishScheduledPost::dispatch($scheduledPost, $connection);
                $queued++;
            }
        }

        return response()->json([
            'message' => "Queued {$queued} posts for publishing",
            'queued' => $queued,
            'total' => count($validated['connection_ids']),
        ]);
    }
}


