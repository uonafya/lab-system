<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // dd('What the hell do you think you are doing!! We see you!!!');
        
        // DB::unprepared(File::get(base_path('database/seeds/complete.sql')));
        // DB::unprepared(File::get(base_path('database/dumps/complete.sql')));
        // DB::unprepared(File::get(base_path('database/seeds/facilitys.sql')));
        // DB::unprepared(File::get(base_path('database/dumps/fac.sql')));

        // DB::unprepared(File::get(base_path('database/seeds/dr.sql')));
        // DB::unprepared(File::get(base_path('database/seeds/dr_tables.sql')));
        // DB::unprepared(File::get(base_path('database/seeds/views.sql')));

        // $this->call(UsersTableSeeder::class);
        // $this->call(FakerSeeder::class);        
        $this->call(DrSeeder::class);
        // $this->call(KitsSeeder::class);    
        // $this->call(LabEquipmentMailingSeeder::class);
        // $this->call(GeneralConsumablesSeeder::class);

        // $this->call(CovidLookupSeeder::class);
        
        // $this->call(CovidKitsSeeder::class);
        // $this->call(TestTypeSeeder::class);
        // $this->call(ManualMachineSeeder::class);
        // $this->call(CancerLookupsSeeder::class);
    }
}
