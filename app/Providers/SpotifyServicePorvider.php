<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SpotifyService;

class SpotifyServicePorvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // dd(SpotifyService::class);
// dd(new SpotifyService());

        $this->app->bind(SpotifyService::class, function($app) {
            return new SpotifyService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
