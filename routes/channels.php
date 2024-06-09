<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('message-sent-channel.{uuid}', function ($user, $uuid) {
    return (int) $user->uuid === (int) $uuid;
});

Broadcast::channel('online-users', function ($user){
    return [
        'uuid' => $user->uuid,
        'id' => $user->id,
        'name' => $user->name,
    ];
});
