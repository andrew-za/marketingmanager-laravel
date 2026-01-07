<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('organization.{organizationId}', function ($user, $organizationId) {
    return $user->hasAccessToOrganization($organizationId);
});

Broadcast::channel('chat.topic.{topicId}', function ($user, $topicId) {
    $topic = \App\Models\ChatTopic::find($topicId);
    
    if (!$topic) {
        return false;
    }
    
    if (!$user->hasAccessToOrganization($topic->organization_id)) {
        return false;
    }
    
    if (!$topic->is_private) {
        return true;
    }
    
    return $topic->created_by === $user->id 
        || $topic->participants()->where('user_id', $user->id)->exists();
});

