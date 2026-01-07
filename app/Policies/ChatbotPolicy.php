<?php

namespace App\Policies;

use App\Models\Chatbot;
use App\Models\User;

class ChatbotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('chatbots.view');
    }

    public function view(User $user, Chatbot $chatbot): bool
    {
        return $user->hasAccessToOrganization($chatbot->organization_id)
            && $user->hasPermissionTo('chatbots.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('chatbots.create');
    }

    public function update(User $user, Chatbot $chatbot): bool
    {
        return $user->hasAccessToOrganization($chatbot->organization_id)
            && $user->hasPermissionTo('chatbots.update');
    }

    public function delete(User $user, Chatbot $chatbot): bool
    {
        return $user->hasAccessToOrganization($chatbot->organization_id)
            && $user->hasPermissionTo('chatbots.delete');
    }
}

