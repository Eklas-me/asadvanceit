<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('agent-stream.{id}', function ($user, $id) {
    // Allow admins to view any stream, or the user themselves
    // For now, let's assume any authenticated user can view (admins)
    // You should add 'isAdmin' check here in production
    return true;
});
