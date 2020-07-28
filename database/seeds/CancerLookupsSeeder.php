<?php

use Illuminate\Database\Seeder;

class CancerLookupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// Sample types
        $sampletypes = [
        	['name' => 'Clinician Collected', 'displaylabel' => '1.&nbps;Clinician Collected'],
            ['name' => 'Self Collected', 'displaylabel' => '2.&nbps;Self Collected'],
        ];
        DB::table('cancersampletypes')->truncate();
        DB::table('cancersampletypes')->insert($sampletypes);

        // Justifications
        $justifications = [
        	['name' => 'Initial Screening', 'displaylabel' => '1.&nbps;Initial Screening'],
        	['name' => 'Routine screening', 'displaylabel' => '2.&nbps;Routine screening'],
        	['name' => 'Post Rx Screening', 'displaylabel' => '3.&nbps;Post Rx Screening'],
        	['name' => 'Repeat Screening', 'displaylabel' => '4.&nbps;Repeat Screening'],
        ];
        DB::table('cancerjustifications')->truncate();
        DB::table('cancerjustifications')->insert($justifications);

        // HIV Statuses
        $hivstatuses = [
        	['name' => 'Reactive ', 'displaylabel' => '1.&nbps;Reactive '],
        	['name' => 'Non Reactive', 'displaylabel' => '2.&nbps;Non Reactive'],
        	['name' => 'Unknown', 'displaylabel' => '3.&nbps;Unknown'],
        ];
        DB::table('cancerhivstatuses')->truncate();
        DB::table('cancerhivstatuses')->insert($hivstatuses);

        // Entry Points
        $entrypoints = [
        	['name' => 'MCH', 'displaylabel' => '1.&nbpsMCH'],
        	['name' => 'CCC', 'displaylabel' => '2.&nbps;CCC'],
        	['name' => 'OPD', 'displaylabel' => '3.&nbps;OPD'],
        ];
        DB::table('cancerentrypoint')->truncate();
        DB::table('cancerentrypoint')->insert($entrypoints);

        // Rejected Reasons
        $rejectedreasons = [
        	['name' => 'Missing Sample', 'displaylabel' => '1.&nbps;Missing Sample'],
	        ['name' => 'Missing patient Number', 'displaylabel' => '2.&nbps;Missing patient Number'],
	        ['name' => 'Sample request form & Sample mismatch', 'displaylabel' => '3.&nbps;Sample request form & Sample mismatch'],
	        ['name' => 'Delayed delivery', 'displaylabel' => '4.&nbps;Delayed delivery'],
	        ['name' => 'Specimen processing delay', 'displaylabel' => '5.&nbps;Specimen processing delay'],
	        ['name' => 'No request form', 'displaylabel' => '6.&nbps;No request form'],
	        ['name' => 'Improper packaging', 'displaylabel' => '7.&nbps;Improper packaging'],
	        ['name' => 'Insufficient volume', 'displaylabel' => '8.&nbps;Insufficient volume'],
	        ['name' => 'Poorly Collected Sample', 'displaylabel' => '9.&nbps;Poorly Collected Sample'],
	        ['name' => 'Incorrect container', 'displaylabel' => '10.&nbps;Incorrect container'],
	        ['name' => 'Other (Specify)', 'displaylabel' => '11.&nbps;Other (Specify)'],
        ];
        DB::table('cancerrejectedreasons')->truncate();
        DB::table('cancerrejectedreasons')->insert($rejectedreasons);

        // Actions
        $actions = [
        	['name' => 'Client given follow up date', 'displaylabel' => '1.&nbps;Client given follow up date'],
	        ['name' => 'Referred for VIA', 'displaylabel' => '2.&nbps;Referred for VIA'],
	        ['name' => 'Referred for Rx (Cryotherapy, LEEP or Thermocoagulation)', 'displaylabel' => '3.&nbps;Referred for Rx (Cryotherapy, LEEP or Thermocoagulation)'],
	        ['name' => 'Client pregnant therefore follow up after delivery.', 'displaylabel' => '4.&nbps;Client pregnant therefore follow up after delivery.'],
        ];
        DB::table('canceractions')->truncate();
        DB::table('canceractions')->insert($actions);
        
    }
}
