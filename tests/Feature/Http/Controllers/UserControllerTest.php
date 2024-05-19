<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('can search users', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'programmer@telo.com')->first();

    $response = $this
                ->getJson('/api/users/search?query=telo');

    $response->assertJsonCount(1, 'users')
        ->assertJson(['users' => [['name' => $user->name]]]);
    $response->assertStatus(200);
});
