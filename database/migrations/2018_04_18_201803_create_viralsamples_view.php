<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralsamplesView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE OR REPLACE VIEW viralsamples_view AS
        (
          SELECT s.*, b.national_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, b.facility_id,
          p.national_patient_id, p.patient, p.patient_name, p.initiation_date, p.sex, p.dob

          FROM viralsamples s
            JOIN viralbatches b ON b.id=s.batch_id
            JOIN viralpatients p ON p.id=s.patient_id

        );
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS viralsamples_view');
    }
}
