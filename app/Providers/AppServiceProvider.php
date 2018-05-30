<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Mother;
use App\Batch;
use App\Viralbatch;
use App\Patient;
use App\Viralpatient;

use App\Sample;
use App\Viralsample;
use App\Worksheet;
use App\Viralworksheet;

use App\Facility;

use App\Observers\MotherObserver;
use App\Observers\BatchObserver;
use App\Observers\ViralbatchObserver;
use App\Observers\PatientObserver;
use App\Observers\ViralpatientObserver;

use App\Observers\SampleObserver;
use App\Observers\ViralsampleObserver;
use App\Observers\WorksheetObserver;
use App\Observers\ViralworksheetObserver;

use App\Observers\FacilityObserver;

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


        
        // Sample::observe(SampleObserver::class);
        // Viralsample::observe(ViralsampleObserver::class);
        
        // Worksheet::observe(WorksheetObserver::class);
        // Viralworksheet::observe(ViralworksheetObserver::class);
        
        // Facility::observe(FacilityObserver::class);
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
