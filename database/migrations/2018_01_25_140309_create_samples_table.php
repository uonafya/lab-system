<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index();
            $table->integer('batch_id')->unsigned()->index();
            $table->string('amrs_location')->nullable();
            $table->string('provider_identifier')->nullable();
            $table->integer('receivedstatus')->unsigned()->nullable()->index();
            $table->double('age', 6, 4)->unsigned()->nullable()->index();
            $table->integer('pcrtype')->unsigned()->nullable()->index();
            $table->integer('regimen')->unsigned()->nullable()->index();
            $table->integer('mother_prophylaxis')->unsigned()->index();
            $table->integer('feeding')->unsigned()->index();
            $table->integer('spots')->unsigned()->nullable();
            $table->string('comments')->nullable();
            $table->string('labcomment')->nullable();
            $table->integer('parentid')->unsigned()->nullable();
            $table->integer('rejectedreason')->unsigned()->nullable();
            $table->integer('reason_for_repeat')->unsigned()->nullable();
            $table->string('intepretation')->nullable();
            $table->integer('result')->unsigned()->nullable()->index();

            $table->integer('worksheet_id')->unsigned()->nullable();
            $table->boolean('inworksheet')->default(false);

            $table->integer('hei_validation')->unsigned()->nullable()->index();
            $table->string('enrollment_ccc_no')->nullable();
            $table->integer('enrollment_status')->unsigned()->nullable()->index();
            $table->integer('referredfromsite')->unsigned()->nullable();
            $table->string('otherreason')->nullable();

            $table->integer('flag')->unsigned()->nullable();
            $table->integer('run')->unsigned()->nullable();
            $table->integer('repeatt')->unsigned()->nullable();
            $table->integer('eqa')->unsigned()->nullable();

            $table->integer('approvedby')->unsigned()->nullable();
            $table->integer('approvedby2')->unsigned()->nullable();

            $table->date('datecollected')->nullable()->index();
            $table->date('datetested')->nullable()->index();
            $table->date('datemodified')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();
            $table->date('dateinitiatedontreatment')->nullable();

            $table->boolean('synched')->default(false);
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
        Schema::dropIfExists('samples');
    }
}
