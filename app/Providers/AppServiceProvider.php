<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Batch;
use App\Viralbatch;

use App\Observers\BatchObserver;
use App\Observers\ViralbatchObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Batch::observe(BatchObserver::class);
        Viralbatch::observe(ViralbatchObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
