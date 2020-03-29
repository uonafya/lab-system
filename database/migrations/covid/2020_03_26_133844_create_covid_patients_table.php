<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCovidPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('covid_patients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('national_patient_id')->index()->nullable();
            $table->integer('facility_id')->nullable();
            $table->integer('case_id')->nullable();
            $table->tinyInteger('nationality')->nullable();
            $table->tinyInteger('identifier_type')->nullable();
            $table->string('identifier', 30)->index();
            $table->string('patient_name')->nullable();
            $table->string('occupation', 80)->nullable();

            $table->tinyInteger('justification')->nullable();

            $table->string('county', 20)->nullable();
            $table->string('subcounty', 30)->nullable();
            $table->string('ward', 30)->nullable();
            $table->string('residence', 40)->nullable();

            $table->string('hospital_admitted', 40)->nullable();

            $table->date('dob')->nullable();
            $table->tinyInteger('sex')->nullable();

            $table->tinyInteger('current_health_status')->nullable();

            $table->date('date_symptoms')->nullable();
            $table->date('date_admission')->nullable();
            $table->date('date_isolation')->nullable();
            $table->date('date_death')->nullable();

            $table->tinyInteger('synched')->default(0)->nullable();
            $table->date('datesynched')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('covid_patients');
    }
}
