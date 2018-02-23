<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralpatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viralpatients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('patient_name')->nullable();
            $table->integer('facility_id')->unsigned()->index();
            $table->string('patient');
            $table->string('fullnames')->nullable();
            $table->string('caregiver_phone')->nullable();
            $table->string('gender')->nullable();
            $table->tinyInteger('sex')->unsigned()->index();
            $table->date('dob')->index();
            $table->date('initiation_date')->nullable();
            $table->boolean('synched')->default(false);
            $table->date('datesynched')->nullable();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viralpatients');
    }
}
