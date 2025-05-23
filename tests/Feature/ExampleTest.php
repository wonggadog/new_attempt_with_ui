<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Create a user and authenticate
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
