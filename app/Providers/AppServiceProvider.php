<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Mother;
use App\Batch;
use App\Viralbatch;
use App\Patient;
use App\Viralpatient;

use App\Observers\MotherObserver;
use App\Observers\BatchObserver;
use App\Observers\ViralbatchObserver;
use App\Observers\PatientObserver;
use App\Observers\ViralpatientObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Mother::observe(MotherObserver::class);

        Batch::observe(BatchObserver::class);
        Viralbatch::observe(ViralbatchObserver::class);
        
        Patient::observe(PatientObserver::class);
        Viralpatient::observe(ViralpatientObserver::class);
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
