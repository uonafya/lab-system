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
            // ['layouts.sidenav','home','layouts.topnav'],'App\Http\ViewComposers\DashboardComposer'
            ['layouts.sidenav'],'App\Http\ViewComposers\DashboardComposerChanged'
        );

        view()->composer(
            'home', 'App\Http\ViewComposers\DashboardComposerChanged@tasks'
        );

        // view()->composer(
        //     'layouts.master', 'App\Http\ViewComposers\DashboardComposer@users'
        // );
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
