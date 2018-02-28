<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(
            ['layouts.sidenav','home'],'App\Http\ViewComposers\DashboardComposer'
        );

        view()->composer(
            'home', 'App\Http\ViewComposers\DashboardComposer@tasks'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
