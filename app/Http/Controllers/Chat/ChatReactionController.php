<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatReactionController extends Controller
{
    public function addReaction(Request $request, ChatMessage $chatMessage): JsonResponse
    {
        $request->validate([
            'reaction' => ['required', 'string', 'max:50'],
        ]);

        $reaction = ChatReaction::firstOrCreate(
            [
                'chat_message_id' => $chatMessage->id,
                'user_id' => $request->user()->id,
                'reaction' => $request->reaction,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $reaction->load('user'),
            'message' => 'Reaction added successfully.',
        ], 201);
    }

    public function removeReaction(Request $request, ChatMessage $chatMessage, string $reaction): JsonResponse
    {
        $deleted = ChatReaction::where('chat_message_id', $chatMessage->id)
            ->where('user_id', $request->user()->id)
            ->where('reaction', $reaction)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Reaction removed successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Reaction not found.',
        ], 404);
    }

    public function getReactions(ChatMessage $chatMessage): JsonResponse
    {
        $reactions = $chatMessage->reactions()
            ->with('user')
            ->get()
            ->groupBy('reaction')
            ->map(function ($group) {
                return [
                    'reaction' => $group->first()->reaction,
                    'count' => $group->count(),
                    'users' => $group->pluck('user'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $reactions,
        ]);
    }
}

