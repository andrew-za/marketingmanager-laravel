<?php

namespace App\Services\Chat;

use App\Models\ChatMessage;
use App\Models\ChatTopic;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    public function parseMentions(string $message, ChatTopic $topic): array
    {
        $mentionedUserIds = [];
        preg_match_all('/@(\w+)/', $message, $matches);
        
        if (!empty($matches[1])) {
            $usernames = $matches[1];
            $participantIds = $topic->participants()->pluck('user_id')->toArray();
            
            $users = User::whereIn('id', $participantIds)
                ->where(function ($query) use ($usernames) {
                    foreach ($usernames as $username) {
                        $query->orWhere('name', 'like', '%' . $username . '%')
                            ->orWhere('email', 'like', '%' . $username . '%');
                    }
                })
                ->pluck('id')
                ->toArray();
            
            $mentionedUserIds = array_unique($users);
        }
        
        return $mentionedUserIds;
    }

    public function sendMentionNotifications(ChatMessage $message, array $mentionedUserIds, User $sender): void
    {
        foreach ($mentionedUserIds as $userId) {
            if ($userId !== $sender->id) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'mention',
                    'title' => 'You were mentioned',
                    'message' => $sender->name . ' mentioned you in ' . $message->topic->name,
                    'data' => [
                        'chat_message_id' => $message->id,
                        'chat_topic_id' => $message->chat_topic_id,
                        'sender_id' => $sender->id,
                    ],
                ]);
            }
        }
    }

    public function handleFileUploads($files, ChatTopic $topic): array
    {
        $attachments = [];
        
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $fileName = $file->getClientOriginalName();
                    $filePath = $file->store('chat-attachments/' . $topic->id, 'public');
                    $fileSize = $file->getSize();
                    $fileType = $file->getMimeType();
                    
                    $attachments[] = [
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'file_type' => $fileType,
                        'file_size' => $fileSize,
                        'url' => Storage::disk('public')->url($filePath),
                    ];
                }
            }
        }
        
        return $attachments;
    }
}

