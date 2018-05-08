<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('national_patient_id')->unsigned()->nullable()->index();
            $table->string('patient', 25);

            // This is when the patient is enrolled into ccc program
            $table->string('ccc_no', 25);
            $table->string('patient_name', 50)->nullable();
            $table->bigInteger('mother_id')->unsigned()->index();
            $table->integer('entry_point')->unsigned()->nullable();
            $table->integer('facility_id')->unsigned();
            $table->string('caregiver_phone', 15)->nullable();
            $table->tinyInteger('sex')->unsigned();
            $table->date('dob')->nullable();
            $table->date('dateinitiatedontreatment')->nullable();
            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();

            // $table->date('created_at')->nullable();
            // $table->date('updated_at')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'patient'], 'eid_patient_unq_index');
            
            // $table->foreign('mother_id')->references('id')->on('mothers');
            // $table->foreign('facility_id')->references('id')->on('facilitys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
}
