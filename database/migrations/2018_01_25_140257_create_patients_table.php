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
            $table->increments('id');
            $table->string('patient_name')->nullable();
            $table->integer('mother_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->string('patient');
            $table->string('fullnames')->nullable();
            $table->string('caregiver_phone')->nullable();
            $table->string('gender');
            $table->date('dob')->nullable();
            $table->boolean('synched')->default(false);
            $table->date('datesynched')->nullable();

            $table->timestamps();

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
