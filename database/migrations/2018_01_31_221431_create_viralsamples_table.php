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
            $table->bigIncrements('id'); 
            $table->bigInteger('national_sample_id')->unsigned()->nullable();
            $table->bigInteger('patient_id')->unsigned()->index();
            $table->bigInteger('batch_id')->unsigned()->index();
            $table->tinyInteger('amrs_location')->nullable();
            $table->string('provider_identifier', 50)->nullable();
            $table->string('order_no', 30)->nullable();
            $table->tinyInteger('vl_test_request_no')->nullable();
            $table->tinyInteger('receivedstatus')->unsigned()->nullable()->index();            

            // This will be used instead
            $table->double('age', 5, 2)->unsigned()->nullable()->index();
            $table->tinyInteger('age_category')->unsigned()->default(0)->index();
            $table->tinyInteger('justification')->unsigned()->nullable()->index();
            $table->string('other_justification', 50)->nullable();
            $table->tinyInteger('sampletype')->unsigned()->nullable()->index();
            $table->tinyInteger('prophylaxis')->unsigned()->nullable()->index();
            $table->tinyInteger('regimenline')->unsigned()->nullable()->index();
            $table->tinyInteger('pmtct')->unsigned()->index()->default(3);

            $table->tinyInteger('dilutionfactor')->unsigned()->nullable();
            $table->tinyInteger('dilutiontype')->unsigned()->nullable();

            $table->string('comments', 30)->nullable();
            $table->string('labcomment', 100)->nullable();
            $table->bigInteger('parentid')->unsigned()->default(0)->index();
            // $table->tinyInteger('spots')->unsigned()->nullable();
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();
            $table->string('reason_for_repeat', 50)->nullable();
            $table->tinyInteger('rcategory')->unsigned()->nullable()->index();

            $table->string('result', 50)->nullable()->index();
            $table->string('units', 30)->nullable();
            $table->string('interpretation', 50)->nullable();

            $table->bigInteger('worksheet_id')->unsigned()->nullable()->index();
            // $table->boolean('inworksheet')->default(false);

            $table->tinyInteger('flag')->unsigned()->default(1)->nullable();
            $table->tinyInteger('run')->unsigned()->default(1)->nullable();
            $table->tinyInteger('repeatt')->unsigned()->default(0)->nullable();
            // $table->tinyInteger('eqa')->unsigned()->default(0)->nullable();

            $table->integer('approvedby')->unsigned()->nullable();
            $table->integer('approvedby2')->unsigned()->nullable();

            $table->date('datecollected')->nullable()->index();
            $table->date('datetested')->nullable()->index();
            $table->date('datemodified')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();

            $table->tinyInteger('tat1')->unsigned()->default(0)->nullable();
            $table->tinyInteger('tat2')->unsigned()->default(0)->nullable();
            $table->tinyInteger('tat3')->unsigned()->default(0)->nullable();
            $table->tinyInteger('tat4')->unsigned()->default(0)->nullable();

            $table->tinyInteger('synched')->default(0)->nullable();
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
        Schema::dropIfExists('viralsamples');
    }
}
