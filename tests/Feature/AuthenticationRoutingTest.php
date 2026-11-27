<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AuthenticationRoutingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_is_an_inertia_page(): void
    {
        $this->withoutVite();
        config()->set('inertia.pages.paths', [resource_path('js/Pages')]);

        $this->get('/login')->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->component('Login'),
        );
    }

    public function test_login_creates_a_session_and_logout_invalidates_it(): void
    {
        $user = User::factory()->create(['email' => 'jane@example.com']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->getJson("/users/{$user->id}")->assertOk()->assertJsonPath('id', $user->id);

        $this->post('/logout')->assertRedirect('/');

        $this->assertGuest();
        $this->getJson("/users/{$user->id}")->assertUnauthorized();
    }

    public function test_invalid_credentials_do_not_authenticate(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_attempts_are_limited_across_web_and_api_routes(): void
    {
        $credentials = ['email' => 'missing@example.com', 'password' => 'incorrect'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/login', $credentials)->assertUnprocessable();
        }

        $this->postJson('/users/login', $credentials)->assertTooManyRequests();
        $this->assertGuest();
    }

    public function test_legacy_api_login_does_not_create_a_browser_session(): void
    {
        $user = User::factory()->create();

        $this->postJson('/users/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertJsonPath('id', $user->id);

        $this->assertGuest();
        $this->getJson("/users/{$user->id}")->assertUnauthorized();
    }

    public function test_public_craft_reads_and_guest_access_to_protected_routes(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        $this->getJson('/crafts')->assertOk()->assertJsonPath('0.name', 'Plumbing');
        $this->getJson("/crafts/$id")->assertOk()->assertJsonPath('name', 'Plumbing');
        $this->get('/users')->assertRedirect('/login');
        $this->getJson('/users')->assertUnauthorized();
        $this->getJson('/users/1')->assertUnauthorized();
        $this->patchJson('/users/1', ['name' => 'Changed'])->assertUnauthorized();
        $this->deleteJson('/users/1')->assertUnauthorized();
        $this->postJson('/crafts', ['name' => 'Electrical'])->assertUnauthorized();
        $this->patchJson("/crafts/$id", ['name' => 'Electrical'])->assertUnauthorized();
        $this->deleteJson("/crafts/$id")->assertUnauthorized();
        $this->assertDatabaseHas('crafts', ['id' => $id, 'name' => 'Plumbing']);
    }

    public function test_non_admin_roles_cannot_manage_crafts_or_list_users(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        foreach (['normal', 'provider', 'plus'] as $type) {
            $user = User::factory()->create();
            $user->forceFill(['user_type' => $type])->save();
            $this->actingAs($user);

            $this->getJson('/users')->assertForbidden();
            $this->postJson('/crafts', ['name' => 'Electrical'])->assertForbidden();
            $this->patchJson("/crafts/$id", ['name' => 'Electrical'])->assertForbidden();
            $this->deleteJson("/crafts/$id")->assertForbidden();
        }

        $this->assertDatabaseCount('crafts', 1);
        $this->assertDatabaseHas('crafts', ['id' => $id, 'name' => 'Plumbing']);
    }

    public function test_admin_can_manage_crafts_and_users(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $this->actingAs($admin);

        $this->getJson('/users')->assertOk()->assertJsonFragment(['id' => $user->id]);
        $this->getJson("/users/{$user->id}")->assertOk()->assertJsonPath('id', $user->id);
        $this->patchJson("/users/{$user->id}", ['name' => 'Updated'])->assertOk()->assertJsonPath('name', 'Updated');
        $response = $this->postJson('/crafts', ['name' => 'Plumbing'])->assertOk();
        $id = $response->json('id');
        $this->patchJson("/crafts/$id", ['name' => 'Electrical'])->assertOk()->assertJsonPath('name', 'Electrical');
        $this->deleteJson("/crafts/$id")->assertOk();
        $this->deleteJson("/users/{$user->id}")->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('crafts', ['id' => $id]);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_users_cannot_access_or_modify_other_accounts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['name' => 'Original']);
        $this->actingAs($user);

        $this->getJson("/users/{$other->id}")->assertForbidden();
        $this->patchJson("/users/{$other->id}", ['name' => 'Changed'])->assertForbidden();
        $this->deleteJson("/users/{$other->id}")->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $other->id, 'name' => 'Original']);
    }

    public function test_users_can_update_their_account_but_cannot_grant_admin_privileges(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->patchJson("/users/{$user->id}", [
            'name' => 'Updated',
            'phone' => '+1 (555) 123-4567',
            'password' => 'new-password',
            'is_admin' => true,
            'user_type' => 'plus',
        ])->assertOk()->assertJsonPath('name', 'Updated')->assertJsonMissingPath('password');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '15551234567',
            'is_admin' => false,
            'user_type' => 'normal',
        ]);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_account_updates_reject_duplicate_email_and_normalized_phone(): void
    {
        $user = User::factory()->create(['phone' => '5550000']);
        $other = User::factory()->create(['email' => 'other@example.com', 'phone' => '15551234567']);

        $this->actingAs($user)->patchJson("/users/{$user->id}", ['email' => $other->email])
            ->assertUnprocessable()->assertJsonValidationErrors(['email']);
        $this->patchJson("/users/{$user->id}", ['phone' => '+1 (555) 123-4567'])
            ->assertUnprocessable()->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone' => '5550000']);
    }

    public function test_deleting_own_account_ends_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson("/users/{$user->id}")->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertGuest();
    }

    public function test_registration_cannot_grant_admin_privileges(): void
    {
        $this->postJson('/users/register', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'password',
            'is_admin' => true,
        ])->assertOk();

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'is_admin' => false]);
    }

    public function test_web_login_and_admin_mutations_require_csrf_tokens(): void
    {
        $this->app->bind(PreventRequestForgery::class, CsrfEnabledForAuthenticationTest::class);
        $admin = $this->admin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertStatus(419);
        $this->actingAs($admin)->postJson('/crafts', ['name' => 'Plumbing'])->assertStatus(419);

        $this->assertDatabaseCount('crafts', 0);
    }

    public function test_valid_csrf_tokens_allow_web_login_and_admin_mutations(): void
    {
        $this->app->bind(PreventRequestForgery::class, CsrfEnabledForAuthenticationTest::class);
        $admin = $this->admin();
        $token = 'test-csrf-token';

        $this->withSession(['_token' => $token])->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            '_token' => $token,
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($admin);

        $this->withSession(['_token' => $token])->postJson('/crafts', [
            'name' => 'Plumbing',
            '_token' => $token,
        ])->assertOk();
        $this->assertDatabaseHas('crafts', ['name' => 'Plumbing']);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}

class CsrfEnabledForAuthenticationTest extends PreventRequestForgery
{
    protected function runningUnitTests()
    {
        return false;
    }
}
