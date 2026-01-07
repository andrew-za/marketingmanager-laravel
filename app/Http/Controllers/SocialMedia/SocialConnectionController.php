<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\SocialConnection;
use App\Services\SocialMedia\ConnectionMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialConnectionController extends Controller
{
    protected ConnectionMonitoringService $monitoringService;

    public function __construct(ConnectionMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index(Request $request, string $organizationId)
    {
        $connections = SocialConnection::where('organization_id', $organizationId)
            ->with('channel')
            ->get();

        return response()->json($connections);
    }

    public function show(SocialConnection $connection)
    {
        $this->authorize('view', $connection);

        $connection->load('channel', 'publishedPosts');

        return response()->json($connection);
    }

    public function checkStatus(SocialConnection $connection)
    {
        $this->authorize('update', $connection);

        $isValid = $this->monitoringService->checkConnectionStatus($connection);

        return response()->json([
            'status' => $connection->status,
            'is_connected' => $isValid,
            'is_expired' => $connection->isExpired(),
        ]);
    }

    public function destroy(SocialConnection $connection)
    {
        $this->authorize('delete', $connection);

        $connection->delete();

        return response()->json(['message' => 'Connection deleted successfully']);
    }
}


