<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('facility')->unsigned()->comment('Facility Code');
            $table->tinyInteger('lab')->unsigned()->comment('Lab ID');
            $table->integer('request');
            $table->integer('supply');
            $table->string('comments', 50)->nullable()->comment('Requisition Comments');
            $table->integer('createdby')->unsigned();
            $table->integer('approvedby')->unsigned()->nullable();
            $table->string('approvecomments', 100)->nullable();
            $table->string('disapprovecomments', 100)->nullable();
            $table->integer('status');
            $table->tinyInteger('flag')->default(1);
            $table->integer('parentid');
            $table->date('requisitiondate');
            $table->date('datesubmitted');
            $table->integer('submittedby')->unsigned()->nullable();
            $table->date('dateapproved')->nullable();
            // $table->date('datesynchronized')->nullable();
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
        Schema::dropIfExists('requisitions');
    }
}
