<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RuntimeException;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class UserServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_all_users_returns_the_model_collection(): void
    {
        $users = new Collection([Mockery::mock(), Mockery::mock()]);
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('all')->once()->withNoArgs()->andReturn($users);

        $this->assertSame($users, UserService::getAllUsers());
    }

    public function test_get_all_users_returns_an_empty_collection(): void
    {
        $users = new Collection;
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('all')->once()->withNoArgs()->andReturn($users);

        $this->assertSame($users, UserService::getAllUsers());
    }

    public function test_get_user_by_id_returns_the_found_user(): void
    {
        $user = Mockery::mock();
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->assertSame($user, UserService::getUserById(42));
    }

    public function test_get_user_by_id_returns_null_when_user_is_missing(): void
    {
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturnNull();

        $this->assertNull(UserService::getUserById(42));
    }

    public function test_create_user_passes_data_to_the_model_and_returns_the_user(): void
    {
        $data = ['name' => 'Jane', 'email' => 'jane@example.com', 'password' => 'secret'];
        $user = Mockery::mock();
        $query = Mockery::mock();
        $query->shouldReceive('exists')->once()->withNoArgs()->andReturnFalse();
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldReceive('where')->once()->with('email', $data['email'])->andReturn($query);
        $model->shouldReceive('create')->once()->with($data)->andReturn($user);

        $this->assertSame($user, UserService::createUser($data));
    }

    public function test_create_user_normalizes_phone_before_checking_and_creating(): void
    {
        $data = ['email' => 'jane@example.com', 'phone' => '+01 (234) 567-8900'];
        $normalized = ['email' => 'jane@example.com', 'phone' => '012345678900'];
        $user = Mockery::mock();
        $emailQuery = Mockery::mock();
        $emailQuery->shouldReceive('exists')->once()->andReturnFalse();
        $phoneQuery = Mockery::mock();
        $phoneQuery->shouldReceive('exists')->once()->andReturnFalse();
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldReceive('where')->once()->with('email', $data['email'])->andReturn($emailQuery);
        $model->shouldReceive('where')->once()->with('phone', $normalized['phone'])->andReturn($phoneQuery);
        $model->shouldReceive('create')->once()->with($normalized)->andReturn($user);

        $this->assertSame($user, UserService::createUser($data));
    }

    public function test_create_user_rejects_duplicate_email(): void
    {
        $query = Mockery::mock();
        $query->shouldReceive('exists')->once()->andReturnTrue();
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldReceive('where')->once()->with('email', 'jane@example.com')->andReturn($query);
        $model->shouldNotReceive('create');

        try {
            UserService::createUser(['email' => 'jane@example.com', 'phone' => '555-1234']);
            $this->fail('A duplicate email must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('email', $exception->errors());
        }
    }

    public function test_create_user_rejects_duplicate_normalized_phone(): void
    {
        $emailQuery = Mockery::mock();
        $emailQuery->shouldReceive('exists')->once()->andReturnFalse();
        $phoneQuery = Mockery::mock();
        $phoneQuery->shouldReceive('exists')->once()->andReturnTrue();
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldReceive('where')->once()->with('email', 'jane@example.com')->andReturn($emailQuery);
        $model->shouldReceive('where')->once()->with('phone', '5551234')->andReturn($phoneQuery);
        $model->shouldNotReceive('create');

        try {
            UserService::createUser(['email' => 'jane@example.com', 'phone' => '555-1234']);
            $this->fail('A duplicate phone must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('phone', $exception->errors());
        }
    }

    public function test_create_user_allows_a_null_phone_without_querying_it(): void
    {
        $data = ['email' => 'jane@example.com', 'phone' => null];
        $user = Mockery::mock();
        $query = Mockery::mock();
        $query->shouldReceive('exists')->once()->andReturnFalse();
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldReceive('where')->once()->with('email', $data['email'])->andReturn($query);
        $model->shouldReceive('create')->once()->with($data)->andReturn($user);

        $this->assertSame($user, UserService::createUser($data));
    }

    public function test_create_user_rejects_a_phone_without_digits(): void
    {
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldNotReceive('where');
        $model->shouldNotReceive('create');

        try {
            UserService::createUser(['phone' => ' ()- ']);
            $this->fail('A phone without digits must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('phone', $exception->errors());
        }
    }

    public function test_create_user_rejects_a_non_string_phone(): void
    {
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldNotReceive('where');
        $model->shouldNotReceive('create');

        try {
            UserService::createUser(['phone' => ['555-1234']]);
            $this->fail('A non-string phone must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('phone', $exception->errors());
        }
    }

    public function test_update_user_updates_and_returns_the_found_user(): void
    {
        $data = ['name' => 'Updated', 'phone' => '1234567890'];
        $user = Mockery::mock();
        $user->shouldReceive('update')->once()->with($data)->andReturnTrue();
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->assertSame($user, UserService::updateUser(42, $data));
    }

    public function test_update_user_returns_null_when_user_is_missing(): void
    {
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturnNull();

        $this->assertNull(UserService::updateUser(42, ['name' => 'Updated']));
    }

    public function test_update_user_normalizes_phone_before_updating(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('update')->once()->with(['phone' => '01234567'])->andReturnTrue();
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->assertSame($user, UserService::updateUser(42, ['phone' => '0 (123) 45-67']));
    }

    public function test_update_user_can_clear_the_phone(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('update')->once()->with(['phone' => null])->andReturnTrue();
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->assertSame($user, UserService::updateUser(42, ['phone' => null]));
    }

    public function test_update_user_rejects_a_phone_without_digits(): void
    {
        $user = Mockery::mock();
        $user->shouldNotReceive('update');
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        try {
            UserService::updateUser(42, ['phone' => '--']);
            $this->fail('A phone without digits must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('phone', $exception->errors());
        }
    }

    public function test_update_user_returns_the_user_when_update_returns_false(): void
    {
        $data = ['name' => 'Updated'];
        $user = Mockery::mock();
        $user->shouldReceive('update')->once()->with($data)->andReturnFalse();
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->assertSame($user, UserService::updateUser(42, $data));
    }

    public function test_delete_user_deletes_and_returns_the_found_user(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('delete')->once()->withNoArgs()->andReturnTrue();
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->assertSame($user, UserService::deleteUser(42));
    }

    public function test_delete_user_returns_null_when_user_is_missing(): void
    {
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturnNull();

        $this->assertNull(UserService::deleteUser(42));
    }

    public function test_delete_user_returns_the_user_when_delete_returns_false(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('delete')->once()->withNoArgs()->andReturnFalse();
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->assertSame($user, UserService::deleteUser(42));
    }

    public function test_create_user_propagates_model_exceptions(): void
    {
        $data = ['email' => 'jane@example.com'];
        $exception = new RuntimeException('Creation failed');
        $query = Mockery::mock();
        $query->shouldReceive('exists')->once()->andReturnFalse();
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldReceive('where')->once()->with('email', $data['email'])->andReturn($query);
        $model->shouldReceive('create')->once()->with($data)->andThrow($exception);

        $this->expectExceptionObject($exception);
        UserService::createUser($data);
    }

    public function test_create_user_propagates_duplicate_check_exceptions_without_creating(): void
    {
        $exception = new RuntimeException('Lookup failed');
        $query = Mockery::mock();
        $query->shouldReceive('exists')->once()->andThrow($exception);
        $model = Mockery::mock('alias:'.User::class);
        $model->shouldReceive('where')->once()->with('email', 'jane@example.com')->andReturn($query);
        $model->shouldNotReceive('create');

        $this->expectExceptionObject($exception);
        UserService::createUser(['email' => 'jane@example.com']);
    }

    public function test_get_all_users_propagates_model_exceptions(): void
    {
        $exception = new RuntimeException('Listing failed');
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('all')->once()->withNoArgs()->andThrow($exception);

        $this->expectExceptionObject($exception);
        UserService::getAllUsers();
    }

    public function test_get_user_by_id_propagates_find_exceptions(): void
    {
        $exception = new RuntimeException('Lookup failed');
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andThrow($exception);

        $this->expectExceptionObject($exception);
        UserService::getUserById(42);
    }

    public function test_update_user_propagates_find_exceptions(): void
    {
        $exception = new RuntimeException('Lookup failed');
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andThrow($exception);

        $this->expectExceptionObject($exception);
        UserService::updateUser(42, ['name' => 'Updated']);
    }

    public function test_update_user_propagates_update_exceptions(): void
    {
        $data = ['name' => 'Updated'];
        $exception = new RuntimeException('Update failed');
        $user = Mockery::mock();
        $user->shouldReceive('update')->once()->with($data)->andThrow($exception);
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->expectExceptionObject($exception);
        UserService::updateUser(42, $data);
    }

    public function test_delete_user_propagates_find_exceptions(): void
    {
        $exception = new RuntimeException('Lookup failed');
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andThrow($exception);

        $this->expectExceptionObject($exception);
        UserService::deleteUser(42);
    }

    public function test_delete_user_propagates_delete_exceptions(): void
    {
        $exception = new RuntimeException('Deletion failed');
        $user = Mockery::mock();
        $user->shouldReceive('delete')->once()->withNoArgs()->andThrow($exception);
        Mockery::mock('alias:'.User::class)
            ->shouldReceive('find')->once()->with(42)->andReturn($user);

        $this->expectExceptionObject($exception);
        UserService::deleteUser(42);
    }
}
