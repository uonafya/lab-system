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
            $table->tinyInteger('receivedstatus')->unsigned()->nullable()->index();
            $table->double('age', 6, 4)->unsigned()->nullable()->index();
            $table->tinyInteger('pcrtype')->unsigned()->nullable()->index();
            $table->tinyInteger('regimen')->unsigned()->nullable()->index();
            $table->tinyInteger('mother_prophylaxis')->unsigned()->index();
            $table->tinyInteger('feeding')->unsigned()->index();
            $table->tinyInteger('spots')->unsigned()->nullable();
            $table->string('comments')->nullable();
            $table->string('labcomment')->nullable();
            $table->integer('parentid')->unsigned()->default(0);
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();
            $table->tinyInteger('reason_for_repeat')->unsigned()->nullable();
            $table->string('interpretation')->nullable();
            $table->tinyInteger('result')->unsigned()->nullable()->index();

            $table->integer('worksheet_id')->unsigned()->nullable();
            $table->boolean('inworksheet')->default(false);

            $table->tinyInteger('hei_validation')->unsigned()->nullable()->index();
            $table->string('enrollment_ccc_no')->nullable();
            $table->tinyInteger('enrollment_status')->unsigned()->nullable()->index();
            $table->tinyInteger('referredfromsite')->unsigned()->nullable();
            $table->string('otherreason')->nullable();

            $table->tinyInteger('flag')->unsigned()->default(1);
            $table->tinyInteger('run')->unsigned()->default(1);
            $table->tinyInteger('repeatt')->unsigned()->default(0);
            $table->tinyInteger('eqa')->unsigned()->default(0);

            $table->integer('approvedby')->unsigned()->nullable();
            $table->integer('approvedby2')->unsigned()->nullable();

            $table->date('datecollected')->nullable()->index();
            $table->date('datetested')->nullable()->index();
            $table->date('datemodified')->nullable()->index();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();

            $table->boolean('synched')->default(false);
            $table->date('datesynched')->nullable();
            // $table->date('created_at')->nullable();
            // $table->date('updated_at')->nullable();
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
