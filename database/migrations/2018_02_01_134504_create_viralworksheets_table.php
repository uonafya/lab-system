<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralWorksheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viralworksheets', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('machine_type')->unsigned();
            $table->tinyInteger('lab_id')->unsigned();

            // 1 is in process
            // 2 is tested, results uploaded awaiting approval
            // 3 is results uploaded and approved
            // 4 is cancelled
            $table->tinyInteger('status_id')->unsigned()->default(1);
            $table->tinyInteger('calibration')->unsigned()->nullable();

            $table->integer('runby')->unsigned()->nullable();
            $table->integer('uploadedby')->unsigned()->nullable();
            $table->integer('sortedby')->unsigned()->nullable();
            $table->integer('alliquotedby')->unsigned()->nullable();
            $table->integer('bulkedby')->unsigned()->nullable();
            $table->integer('reviewedby')->unsigned()->nullable();
            $table->integer('reviewed2by')->unsigned()->nullable();
            $table->integer('createdby')->unsigned()->nullable();
            $table->integer('cancelledby')->unsigned()->nullable();

            $table->string('hiqcap_no')->nullable();
            $table->string('spekkit_no')->nullable();
            $table->string('rack_no')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('sample_prep_lot_no')->nullable();
            $table->string('bulklysis_lot_no')->nullable();
            $table->string('control_lot_no')->nullable();
            $table->string('calibrator_lot_no')->nullable();
            $table->string('amplification_kit_lot_no')->nullable();

            $table->tinyInteger('neg_control_result')->unsigned()->nullable();
            $table->tinyInteger('highpos_control_result')->unsigned()->nullable();
            $table->tinyInteger('lowpos_control_result')->unsigned()->nullable();

            $table->string('neg_control_interpretation')->nullable();
            $table->string('highpos_control_interpretation')->nullable();
            $table->string('lowpos_control_interpretation')->nullable();

            $table->string('neg_units')->nullable();
            $table->string('hpc_units')->nullable();
            $table->string('lpc_units')->nullable();

            $table->string('cdcworksheetno')->nullable();

            $table->date('kitexpirydate')->nullable();
            $table->date('sampleprepexpirydate')->nullable();
            $table->date('bulklysisexpirydate')->nullable();
            $table->date('controlexpirydate')->nullable();
            $table->date('calibratorexpirydate')->nullable();
            $table->date('amplificationexpirydate')->nullable();

            $table->date('datecut')->nullable();
            $table->date('datereviewed')->nullable();
            $table->date('dateuploaded')->nullable();
            $table->date('datecancelled')->nullable();
            $table->date('daterun')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();

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
        Schema::dropIfExists('viralworksheets');
    }
}
