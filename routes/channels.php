<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('agent-stream.{id}', function ($user, $id) {
    return true; // Simple for now
});

Broadcast::channel('agent-monitor.{channelId}', function ($user, $channelId) {
    return $user->role === 'admin';
});
