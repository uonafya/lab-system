<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralsamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viralsamples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index();
            $table->integer('batch_id')->unsigned()->index();
            $table->string('amrs_location')->nullable();
            $table->string('provider_identifier')->nullable();
            $table->tinyInteger('receivedstatus')->unsigned()->nullable()->index();

            // This will be used instead
            $table->tinyInteger('age')->unsigned()->nullable()->index();
            $table->tinyInteger('justification')->unsigned()->nullable()->index();
            $table->tinyInteger('sampletype')->unsigned()->nullable()->index();
            $table->tinyInteger('prophylaxis')->unsigned()->index();
            $table->tinyInteger('regimenline')->unsigned()->index();
            $table->tinyInteger('pmtct')->unsigned()->index()->default(3);

            $table->tinyInteger('dilutionfactor')->unsigned()->nullable();
            $table->tinyInteger('dilutiontype')->unsigned()->nullable();

            $table->string('comments')->nullable();
            $table->string('labcomment')->nullable();
            $table->tinyInteger('spots')->unsigned()->nullable();
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();
            $table->tinyInteger('reason_for_repeat')->unsigned()->nullable();
            $table->tinyInteger('rcategory')->unsigned()->nullable()->index();

            $table->tinyInteger('result')->unsigned()->nullable()->index();
            $table->string('units')->nullable();
            $table->string('interpretation')->nullable();

            $table->integer('worksheet_id')->unsigned()->nullable();
            $table->boolean('inworksheet')->default(false);

            $table->tinyInteger('flag')->unsigned()->nullable();
            $table->tinyInteger('run')->unsigned()->nullable();
            $table->tinyInteger('repeatt')->unsigned()->nullable();
            $table->tinyInteger('eqa')->unsigned()->nullable();

            $table->integer('approvedby')->unsigned()->nullable();
            $table->integer('approvedby2')->unsigned()->nullable();

            $table->date('datecollected')->nullable()->index();
            $table->date('datetested')->nullable()->index();
            $table->date('datemodified')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();

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
        Schema::dropIfExists('viralsamples');
    }
}
