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

            // 1 is in process
            // 2 is tested, results uploaded awaiting approval
            // 3 is results uploaded and approved
            // 4 is cancelled
            $table->tinyInteger('status_id')->unsigned()->default(1)->index();

            $table->date('datereviewed')->nullable();
            $table->date('dateuploaded')->nullable();
            $table->date('datecancelled')->nullable();

            $table->integer('reviewedby')->unsigned()->nullable();
            $table->integer('uploadedby')->unsigned()->nullable();
            $table->integer('cancelledby')->unsigned()->nullable();
            $table->integer('createdby')->unsigned()->nullable();

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
