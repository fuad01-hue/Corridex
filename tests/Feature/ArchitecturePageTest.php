<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchitecturePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_architecture_portfolio_page(): void
    {
        $response = $this->get(route('architecture'));

        $response->assertStatus(200);
        $response->assertSee('System Architecture &amp; Database ERD', false);
        $response->assertSee('architecture-diagram.svg');
        $response->assertSee('erd-diagram.svg');
        $response->assertSee('Corridor 1: SEA');
        $response->assertSee('Corridor 2: ASIA');
        $response->assertSee('Corridor 3: SEA');
    }

    public function test_authenticated_user_can_access_architecture_portfolio_page(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->get(route('architecture'));

        $response->assertStatus(200);
        $response->assertSee('architecture-diagram.svg');
        $response->assertSee('erd-diagram.svg');
        $response->assertSee('Role: customer');
    }

    public function test_diagram_svg_files_are_accessible_in_public_directory(): void
    {
        $this->assertFileExists(public_path('images/diagrams/architecture-diagram.svg'));
        $this->assertFileExists(public_path('images/diagrams/erd-diagram.svg'));
        $this->assertFileExists(base_path('docs/images/architecture-diagram.svg'));
        $this->assertFileExists(base_path('docs/images/erd-diagram.svg'));
    }
}
