<?php

use Illuminate\Support\Facades\Broadcast;

// Private user channel authorization
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private admin channel authorization
Broadcast::channel('admin-channel', function ($user) {
    return $user->isAdmin();
});

// Presence global users channel authorization
Broadcast::channel('presence-global-users', function ($user) {
    if ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
    return false;
});
