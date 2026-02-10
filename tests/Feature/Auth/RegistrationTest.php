<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('register redirects to login', function () {
    $response = $this->get(route('register'));

    $response->assertRedirect(route('login'));
});