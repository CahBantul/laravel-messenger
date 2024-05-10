<?php

use App\Models\User;

test('Authenticated User can view Chat Page', function () {
    $user = User::factory()->create();
    $response = $this
                ->actingAs($user)
                ->get('/chats');

    $response->assertStatus(200);
});

test('Unauthenticated User can not view Chat Page', function () {
    $response = $this
                ->get('/chats');

    $response->assertRedirect(route('login'));
});
