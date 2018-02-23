<?php

use Illuminate\Database\Seeder;

class FakerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //


		$batches = factory(App\Batch::class, 20)->create()
		->each(function ($b){
			$b->sample()->saveMany(
				factory(App\Sample::class, 10)->create(['batch_id' => $b->id])
				->each(function ($s) use ($b){
					$patient = factory(App\Patient::class)->create(['facility_id' => $b->facility_id]);
				})
			);
		});

		// $batch = factory(App\Batch::class)->create();

		$mothers = factory(App\Mother::class, 10)->create();
    }
}
