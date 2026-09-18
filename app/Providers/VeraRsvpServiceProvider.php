<?php

namespace App\Providers;

use App\Http\Middleware\VeraRsvpCors;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class VeraRsvpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Run before the shop's global CORS middleware, but only handle our exact path.
        $this->callAfterResolving(Kernel::class, function ($kernel): void {
            $kernel->prependMiddleware(VeraRsvpCors::class);
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/vera_rsvp.php'));
    }
}
