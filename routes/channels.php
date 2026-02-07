<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('agent-stream.{id}', function ($user, $id) {
    return true; // Simple for now
});

Broadcast::channel('agent-monitor.{type}.{id}', function ($user, $type, $id) {
    // Only admins can monitor
    return $user->role === 'admin' || $user->isAdmin();
});

Broadcast::channel('device-control.{hwid}', function () {
    return true; // Agent is not a Laravel User, so we permit it based on HWID (signaling security handled by endpoint)
});
