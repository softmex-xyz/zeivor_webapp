<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_without_csrf_token_reaches_controller_and_creates_user(): void
    {
        $this->app->bind(PreventRequestForgery::class, CsrfEnabledForRegistrationTest::class);
        $response = $this->postJson('/users/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 (555) 123-4567',
            'password' => 'test-password',
            'user_type' => 'provider',
        ], ['Origin' => 'https://softmex.xyz']);

        $response->assertOk()
            ->assertJsonPath('email', 'jane@example.com')
            ->assertJsonPath('phone', '15551234567')
            ->assertJsonMissingPath('password')
            ->assertHeader('Access-Control-Allow-Origin', 'https://softmex.xyz');
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'phone' => '15551234567',
            'user_type' => 'normal',
        ]);
        $this->assertTrue(Hash::check('test-password', User::where('email', 'jane@example.com')->firstOrFail()->password));
    }

    public function test_invalid_registration_returns_json_and_creates_no_user(): void
    {
        $response = $this->post('/users/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_duplicate_normalized_phone_returns_validation_error(): void
    {
        User::factory()->create(['phone' => '15551234567']);

        $response = $this->postJson('/users/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 (555) 123-4567',
            'password' => 'test-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['phone']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_email_returns_validation_error(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/users/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'test-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_email_and_phone_return_validation_errors(): void
    {
        $response = $this->postJson('/users/register', [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'phone' => ['5551234567'],
            'password' => 'test-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_web_routes_remain_protected_from_csrf(): void
    {
        $this->app->bind(PreventRequestForgery::class, CsrfEnabledForRegistrationTest::class);
        Route::middleware('web')->post('/csrf-test', fn () => response()->noContent());

        $this->post('/csrf-test')->assertStatus(419);
    }
}

class CsrfEnabledForRegistrationTest extends PreventRequestForgery
{
    protected function runningUnitTests()
    {
        return false;
    }
}
