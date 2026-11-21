<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Sleep;
use Illuminate\Validation\Rules\Password;

/**
 * Application-wide defaults, previously provided by nunomaduro/essentials,
 * which requires PHP 8.3. Behaviour is kept identical.
 */
final class ApplicationDefaultsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Vite::useAggressivePrefetching();

        Model::automaticallyEagerLoadRelationships();

        Date::use(CarbonImmutable::class);

        Model::shouldBeStrict();
        Model::unguard();

        DB::prohibitDestructiveCommands($this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceHttps();
        }

        if ($this->app->runningUnitTests()) {
            Sleep::fake();
            Http::preventStrayRequests();

            return;
        }

        Password::defaults(fn (): ?Password => $this->app->isProduction()
            ? Password::min(12)->letters()->mixedCase()->numbers()->symbols()->max(255)->uncompromised()
            : null);
    }
}
