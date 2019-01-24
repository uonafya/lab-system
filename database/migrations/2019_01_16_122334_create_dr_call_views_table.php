<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrCallViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::statement("
        // CREATE OR REPLACE VIEW dr_calls_view AS
        // (
        //   SELECT cd.*, c.sample_id, c.drug_class, c.drug_class_id, c.other_mutations, c.major_mutations,
        //   s.patient_id, s.facility_id 

<<<<<<< HEAD
        //   FROM dr_calls c
        //     LEFT JOIN dr_call_drugs cd ON c.id=cd.call_id
        //     LEFT JOIN dr_samples s ON c.sample_id=s.id
=======
          FROM dr_call_drugs cd
            LEFT JOIN  dr_calls c ON c.id=cd.call_id
            LEFT JOIN dr_samples s ON c.sample_id=s.id
>>>>>>> 8e67f09bcbf4504838fe0f0774e9d450865a0b11

        // );
        // ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('dr_call_views');
    }
}
