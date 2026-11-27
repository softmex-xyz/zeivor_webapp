<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-crafts', fn (User $user): bool => (bool) $user->is_admin);
        Gate::define('manage-users', fn (User $user): bool => (bool) $user->is_admin);
        Gate::define('manage-user', fn (User $user, User $target): bool => $user->is_admin || $user->is($target));

        RateLimiter::for('login', function (Request $request): array {
            $email = $request->input('email');
            $email = is_string($email) ? Str::transliterate(Str::lower($email)) : '';
            $ip = $request->ip();

            return [
                Limit::perMinute(5)->by('account:'.$email.'|'.$ip),
                Limit::perMinute(50)->by('ip:'.$ip),
            ];
        });
    }
}
