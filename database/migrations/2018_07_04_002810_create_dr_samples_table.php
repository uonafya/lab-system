<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dr_samples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index();

            $table->tinyInteger('prev_prophylaxis')->unsigned()->nullable();
            $table->tinyInteger('prophylaxis')->unsigned()->nullable();
            $table->tinyInteger('receivedstatus')->unsigned()->nullable(); 
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();

            // startartdate
            $table->date('date_prev_regimen')->nullable(); 
            $table->date('date_current_regimen')->nullable(); 

            $table->integer('worksheet_id')->nullable()->unsigned()->index();

            $table->date('datecollected')->nullable();
            $table->date('datereceived')->nullable();
            $table->date('datetested')->nullable();
            $table->date('datedispatched')->nullable();

            $table->tinyInteger('dr_reason_id')->unsigned()->index();

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
        Schema::dropIfExists('dr_samples');
    }
}
