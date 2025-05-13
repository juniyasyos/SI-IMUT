<?php

test('example', function () {
    $user = App\Models\User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/');
    $response->assertStatus(200);
});
