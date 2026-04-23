<?php

namespace App\Providers;

use App\Models\Ticket\Ticket;
use App\Models\User;
use App\Policies\Ticket\TicketPolicy;
use App\Policies\User\UserPolicy;
use App\Services\Auth\App\AppAuthService;
use App\Services\Auth\App\AppAuthServiceInterface;
use App\Services\Auth\Web\AuthService;
use App\Services\Auth\Web\AuthServiceInterface;
use App\Services\Ticket\TicketService;
use App\Services\Ticket\TicketServiceInterface;
use App\Services\User\UserService;
use App\Services\User\UserServiceInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(AppAuthServiceInterface::class, AppAuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TicketServiceInterface::class, TicketService::class);
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);

        RateLimiter::for('auth-login', function (Request $request): Limit {
            $maxAttempts = (int)config('auth.rate_limits.login.max_attempts', 5);
            $decaySeconds = (int)config('auth.rate_limits.login.decay_seconds', 60);
            $email = mb_strtolower((string)$request->input('email'));

            return Limit::perMinutes(max(1, (int)ceil($decaySeconds / 60)), $maxAttempts)
                ->by($email . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Слишком много попыток входа. Попробуйте позже.',
                    ], 429, $headers);
                });
        });

        RateLimiter::for('auth-app-login', function (Request $request): Limit {
            $maxAttempts = (int)config('auth.rate_limits.login.max_attempts', 5);
            $decaySeconds = (int)config('auth.rate_limits.login.decay_seconds', 60);
            $email = mb_strtolower((string)$request->input('email'));
            $deviceName = trim((string)$request->input('device_name'));

            return Limit::perMinutes(max(1, (int)ceil($decaySeconds / 60)), $maxAttempts)
                ->by($email . '|' . $deviceName . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Слишком много попыток входа в приложение. Попробуйте позже.',
                    ], 429, $headers);
                });
        });

        RateLimiter::for('auth-register', function (Request $request): Limit {
            $maxAttempts = (int)config('auth.rate_limits.register.max_attempts', 3);
            $decaySeconds = (int)config('auth.rate_limits.register.decay_seconds', 300);

            return Limit::perMinutes(max(1, (int)ceil($decaySeconds / 60)), $maxAttempts)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Слишком много попыток регистрации. Попробуйте позже.',
                    ], 429, $headers);
                });
        });

        RateLimiter::for('auth-app-register', function (Request $request): Limit {
            $maxAttempts = (int)config('auth.rate_limits.register.max_attempts', 3);
            $decaySeconds = (int)config('auth.rate_limits.register.decay_seconds', 300);
            $email = mb_strtolower((string)$request->input('email'));
            $deviceName = trim((string)$request->input('device_name'));

            return Limit::perMinutes(max(1, (int)ceil($decaySeconds / 60)), $maxAttempts)
                ->by($email . '|' . $deviceName . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Слишком много попыток регистрации в приложении. Попробуйте позже.',
                    ], 429, $headers);
                });
        });
    }
}
