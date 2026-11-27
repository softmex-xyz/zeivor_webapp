<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegisterRouteTest extends TestCase
{
    public function test_expert_register_route_renders_register_page(): void
    {
        $response = $this->get('/experto');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Register'));
    }
}

