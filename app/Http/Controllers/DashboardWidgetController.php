<?php

namespace App\Http\Controllers;

use App\Models\DashboardWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardWidgetController extends Controller
{
    public function index(Request $request, string $organizationId): JsonResponse
    {
        $userId = $request->user()->id;

        $widgets = DashboardWidget::where(function ($query) use ($userId, $organizationId) {
            $query->where('user_id', $userId)
                ->orWhere(function ($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId)
                        ->whereNull('user_id');
                });
        })
        ->orderBy('position_y')
        ->orderBy('position_x')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $widgets,
        ]);
    }

    public function store(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'widget_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'config' => 'nullable|array',
            'position_x' => 'integer|min:0',
            'position_y' => 'integer|min:0',
            'width' => 'integer|min:1|max:12',
            'height' => 'integer|min:1|max:12',
            'is_visible' => 'boolean',
        ]);

        $widget = DashboardWidget::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'organization_id' => $organizationId,
            'position_x' => $validated['position_x'] ?? 0,
            'position_y' => $validated['position_y'] ?? 0,
            'width' => $validated['width'] ?? 4,
            'height' => $validated['height'] ?? 4,
            'is_visible' => $validated['is_visible'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $widget,
            'message' => 'Dashboard widget created successfully.',
        ], 201);
    }

    public function update(Request $request, string $organizationId, DashboardWidget $widget): JsonResponse
    {
        if ($widget->user_id != $request->user()->id || $widget->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'config' => 'nullable|array',
            'position_x' => 'sometimes|integer|min:0',
            'position_y' => 'sometimes|integer|min:0',
            'width' => 'sometimes|integer|min:1|max:12',
            'height' => 'sometimes|integer|min:1|max:12',
            'is_visible' => 'boolean',
        ]);

        $widget->update($validated);

        return response()->json([
            'success' => true,
            'data' => $widget,
            'message' => 'Dashboard widget updated successfully.',
        ]);
    }

    public function destroy(string $organizationId, DashboardWidget $widget): JsonResponse
    {
        if ($widget->user_id != request()->user()->id || $widget->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found.',
            ], 404);
        }

        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard widget deleted successfully.',
        ]);
    }

    public function updatePositions(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:dashboard_widgets,id',
            'widgets.*.position_x' => 'required|integer|min:0',
            'widgets.*.position_y' => 'required|integer|min:0',
            'widgets.*.width' => 'sometimes|integer|min:1|max:12',
            'widgets.*.height' => 'sometimes|integer|min:1|max:12',
        ]);

        $userId = $request->user()->id;

        foreach ($validated['widgets'] as $widgetData) {
            $widget = DashboardWidget::find($widgetData['id']);
            
            if ($widget && $widget->user_id == $userId && $widget->organization_id == $organizationId) {
                $widget->update([
                    'position_x' => $widgetData['position_x'],
                    'position_y' => $widgetData['position_y'],
                    'width' => $widgetData['width'] ?? $widget->width,
                    'height' => $widgetData['height'] ?? $widget->height,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Widget positions updated successfully.',
        ]);
    }
}


