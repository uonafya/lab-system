<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCovidWorksheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('covid_worksheets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('national_worksheet_id')->unsigned()->nullable()->index();
            $table->tinyInteger('machine_type')->unsigned();
            $table->tinyInteger('lab_id')->unsigned();

            // 0 for no
            // 1 for with EID
            // 2 for with VL
            $table->tinyInteger('combined')->unsigned()->default(0);

            // 1 is in process
            // 2 is tested, results uploaded awaiting approval
            // 3 is results uploaded and approved
            // 4 is cancelled
            $table->tinyInteger('status_id')->unsigned()->default(1)->index();

            $table->integer('runby')->unsigned()->nullable();
            $table->integer('uploadedby')->unsigned()->nullable();
            $table->integer('sortedby')->unsigned()->nullable();
            $table->integer('alliquotedby')->unsigned()->nullable();
            $table->integer('bulkedby')->unsigned()->nullable();
            $table->integer('reviewedby')->unsigned()->nullable();
            $table->integer('reviewedby2')->unsigned()->nullable();
            $table->integer('createdby')->unsigned()->nullable();
            $table->integer('cancelledby')->unsigned()->nullable();

            $table->string('hiqcap_no', 40)->nullable();
            $table->string('spekkit_no', 40)->nullable();
            $table->string('rack_no', 40)->nullable();
            $table->string('lot_no', 40)->nullable();
            $table->string('sample_prep_lot_no', 40)->nullable();
            $table->string('bulklysis_lot_no', 40)->nullable();
            $table->string('control_lot_no', 40)->nullable();
            $table->string('calibrator_lot_no', 40)->nullable();
            $table->string('amplification_kit_lot_no', 40)->nullable();

            $table->tinyInteger('neg_control_result')->unsigned()->nullable();
            $table->tinyInteger('pos_control_result')->unsigned()->nullable();

            $table->string('neg_control_interpretation', 50)->nullable();
            $table->string('pos_control_interpretation', 50)->nullable();

            $table->string('cdcworksheetno', 40)->nullable();

            $table->date('kitexpirydate')->nullable();
            $table->date('sampleprepexpirydate')->nullable();
            $table->date('bulklysisexpirydate')->nullable();
            $table->date('controlexpirydate')->nullable();
            $table->date('calibratorexpirydate')->nullable();
            $table->date('amplificationexpirydate')->nullable();

            $table->date('datecut')->nullable();
            $table->date('datereviewed')->nullable();
            $table->date('datereviewed2')->nullable();
            $table->date('dateuploaded')->nullable();
            $table->date('datecancelled')->nullable();
            $table->date('daterun')->nullable();

            $table->tinyInteger('synched')->default(0);
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
        Schema::dropIfExists('covid_worksheets');
    }
}
