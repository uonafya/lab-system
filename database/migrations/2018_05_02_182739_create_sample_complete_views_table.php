<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleCompleteViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE OR REPLACE VIEW sample_complete_view AS
        (
          SELECT s.*, b.national_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, b.facility_id, b.batch_complete,
          p.national_patient_id, p.patient, p.sex, p.dob, p.mother_id, p.entry_point, g.gender_description, rs.name as receivedstatus_name, mp.name as mother_prophylaxis_name, ip.name as regimen_name, f.feeding as feeding_name, f.feeding_description

          FROM samples s 
            JOIN batches b ON b.id=s.batch_id
            JOIN patients p ON p.id=s.patient_id
            LEFT JOIN gender g on g.id=p.sex
            LEFT JOIN receivedstatus rs on rs.id=s.receivedstatus
            LEFT JOIN prophylaxis mp on mp.id=s.mother_prophylaxis
            LEFT JOIN prophylaxis ip on ip.id=s.regimen
            LEFT JOIN feedings f on f.id=s.feeding
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
        DB::statement('DROP VIEW IF EXISTS sample_complete_view');
    }
}
