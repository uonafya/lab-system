<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorksheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('worksheets', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('machine_type')->unsigned();
            $table->tinyInteger('lab_id')->unsigned();
            $table->tinyInteger('status_id')->unsigned()->default(1);

            $table->integer('runby')->unsigned()->nullable();
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

            $table->tinyInteger('neg_control_result')->unsigned();
            $table->tinyInteger('pos_control_result')->unsigned();

            $table->string('neg_control_intepretation')->nullable();
            $table->string('pos_control_intepretation')->nullable();

            $table->string('cdcworksheetno')->nullable();

            $table->date('kitexpirydate')->nullable();
            $table->date('sampleprepexpirydate')->nullable();
            $table->date('bulklysisexpirydate')->nullable();
            $table->date('controlexpirydate')->nullable();
            $table->date('calibratorexpirydate')->nullable();
            $table->date('amplificationexpirydate')->nullable();

            $table->date('datecut')->nullable();
            $table->date('datecancelled')->nullable();
            $table->date('daterun')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();

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
        Schema::dropIfExists('worksheets');
    }
}
