<?php

namespace App\Providers;

use App\Listeners\RememberLoginMethod;
use App\Observers\ActivityObserver;
use App\Services\AnthropicQuestionGenerator;
use App\Services\QuestionGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QuestionGenerator::class, AnthropicQuestionGenerator::class);
    }

    public function boot(): void
    {
        $this->configureDefaults();

        RateLimiter::for('meetme-scan', fn (Request $request) => Limit::perMinute((int) config('meetme.scan_rate_limit'))
            ->by((string) $request->user()?->id ?: $request->ip()));

        Event::listen(Login::class, RememberLoginMethod::class);

        Activity::observe(ActivityObserver::class);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
