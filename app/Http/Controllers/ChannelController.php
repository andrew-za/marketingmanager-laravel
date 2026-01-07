<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    public function index(Request $request, string $organizationId)
    {
        $channels = Channel::where('organization_id', $organizationId)
            ->with('socialConnection', 'settings')
            ->get();

        return response()->json($channels);
    }

    public function store(Request $request, string $organizationId)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'type' => 'required|in:email,whatsapp,amplify,paid_ads,press_release,influencer,social',
            'platform' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,disconnected',
            'settings' => 'nullable|array',
        ]);

        $channel = Channel::create([
            'organization_id' => $organizationId,
            'display_name' => $validated['display_name'],
            'type' => $validated['type'],
            'platform' => $validated['platform'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        if (!empty($validated['settings'])) {
            ChannelSetting::create([
                'channel_id' => $channel->id,
                'settings_json' => $validated['settings'],
            ]);
        }

        return response()->json($channel->load('settings'), 201);
    }

    public function show(Channel $channel)
    {
        $this->authorize('view', $channel);

        $channel->load('socialConnection', 'settings');

        return response()->json($channel);
    }

    public function update(Request $request, Channel $channel)
    {
        $this->authorize('update', $channel);

        $validated = $request->validate([
            'display_name' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:active,inactive,disconnected',
            'settings' => 'sometimes|nullable|array',
        ]);

        $channel->update($validated);

        if (isset($validated['settings'])) {
            $setting = $channel->settings;
            if ($setting) {
                $setting->update(['settings_json' => $validated['settings']]);
            } else {
                ChannelSetting::create([
                    'channel_id' => $channel->id,
                    'settings_json' => $validated['settings'],
                ]);
            }
        }

        return response()->json($channel->load('settings'));
    }

    public function destroy(Channel $channel)
    {
        $this->authorize('delete', $channel);

        $channel->delete();

        return response()->json(['message' => 'Channel deleted successfully']);
    }

    public function updateSettings(Request $request, Channel $channel)
    {
        $this->authorize('update', $channel);

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $setting = $channel->settings;
        if ($setting) {
            $setting->update(['settings_json' => $validated['settings']]);
        } else {
            ChannelSetting::create([
                'channel_id' => $channel->id,
                'settings_json' => $validated['settings'],
            ]);
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}


