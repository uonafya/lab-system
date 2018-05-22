<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabEquipmentTrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_equipment_trackers', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('month')->unsigned()->index();
            $table->tinyInteger('year')->unsigned()->index();
            $table->integer('lab_id')->unsigned()->index();
            $table->tinyInteger('equipment_id')->unsigned()->index();
            $table->date('datesubmitted');
            $table->bigInteger('submittedBy');
            $table->date('dateemailsent');
            $table->date('datebrokendown');
            $table->date('datereported');
            $table->date('datefixed');
            $table->integer('downtime')->unsigned()->nullable();
            $table->integer('samplesnorun')->unsigned()->nullable();
            $table->integer('failedruns')->unsigned()->nullable();
            $table->string('reagentswasted', 300)->nullable();
            $table->string('breakdownreason', 300)->nullable();
            $table->string('othercomments', 300)->nullable();
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
        Schema::dropIfExists('lab_equipment_trackers');
    }
}
