<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test root route returns a successful public homepage response with Corridex branding.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CORRIDEX');
        $response->assertSee('Multi-Corridor Freight Visibility Engine');
    }
}
