<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCovidSampleViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        DB::statement("
        CREATE OR REPLACE VIEW covid_sample_view AS
        (
          SELECT s.*, p.facility_id, p.case_id, p.identifier_type, p.identifier, p.patient_name, p.occupation, p.justification, p.county, p.subcounty, p.ward, p.residence, p.hospital_admitted, p.dob, p.sex, p.current_health_status, p.date_symptoms, p.date_admission, p.date_isolation, date_death, `f`.`facilitycode`,`f`.`name` as facilityname, f.partner, f.district
          FROM covid_samples s
            JOIN covid_patients p ON p.id=s.patient_id
            LEFT JOIN facilitys f ON f.id=p.facility_id
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

    }
}
