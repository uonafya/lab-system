<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrWorksheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dr_worksheets', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('lab_id')->unsigned();

            // This is the sanger id
            $table->integer('plate_id')->unsigned()->nullable()->index();
            $table->integer('extraction_worksheet_id')->nullable()->unsigned()->index();

            // 1 is in process
            // 2 is tested, results uploaded awaiting approval
            // 3 is results uploaded and approved
            // 4 is cancelled
            $table->tinyInteger('status_id')->unsigned()->default(1)->index();
            $table->tinyInteger('sanger_status_id')->unsigned()->default(1)->index();

            $table->date('datereviewed')->nullable();
            $table->date('dateuploaded')->nullable();
            $table->date('datecancelled')->nullable();

            $table->integer('reviewedby')->unsigned()->nullable();
            $table->integer('uploadedby')->unsigned()->nullable();
            $table->integer('cancelledby')->unsigned()->nullable();
            $table->integer('createdby')->unsigned()->nullable();


            $table->boolean('qc_pass')->default(0);
            $table->boolean('qc_run')->default(0);
            $table->boolean('plate_controls_pass')->default(0);



            $table->boolean('has_errors')->default(0);
            $table->boolean('has_warnings')->default(0);

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
        Schema::dropIfExists('dr_worksheets');
    }
}
