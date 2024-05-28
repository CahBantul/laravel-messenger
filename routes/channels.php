<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('message-sent-channel.{uuid}', function ($user, $uuid) {
    return (int) $user->uuid === (int) $uuid;
});
