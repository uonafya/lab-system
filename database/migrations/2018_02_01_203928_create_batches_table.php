<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            // $table->bigIncrements('id');
            $table->double('id', 14, 2)->autoIncrement();
            $table->bigInteger('national_batch_id')->unsigned()->nullable()->index();
            $table->boolean('highpriority')->default(false);
            $table->boolean('input_complete')->default(false);
            $table->boolean('batch_full')->default(false); 

            // 0 is default i.e. new
            // 1 is dispatched
            // 2 is staging i.e. all samples are ready, batch awaiting dispatch
            $table->tinyInteger('batch_complete')->unsigned()->default(0);

            // 0 is for lab entry
            // 1 is for site entry
            // 2 is for POC entry
            $table->tinyInteger('site_entry')->unsigned()->default(0);

            $table->boolean('sent_email')->default(false);

            $table->integer('printedby')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('received_by')->unsigned()->nullable();

            // In the event of a POC sample, it will be the facility where the sample was tested
            $table->integer('lab_id')->unsigned()->index();
            $table->integer('facility_id')->unsigned()->index();

            $table->date('datedispatchedfromfacility')->nullable();
            $table->date('datereceived')->nullable()->index();
            $table->date('datedispatched')->nullable()->index();
            $table->date('dateindividualresultprinted')->nullable();
            $table->date('datebatchprinted')->nullable();

            $table->tinyInteger('synched')->default(0);
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
        Schema::dropIfExists('batches');
    }
}
