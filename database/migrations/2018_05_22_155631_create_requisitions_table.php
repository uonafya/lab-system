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
            $table->integer('facility')->comment('Facility Code');
            $table->integer('lab')->comment('Lab ID');
            $table->integer('request');
            $table->integer('supply');
            $table->string('comments', 50)->nullable()->comment('Requisition Comments');
            $table->integer('createdby');
            $table->timestamps();
            $table->integer('approvedby')->nullable();
            $table->string('approvecomments', 100)->nullable();
            $table->string('disapprovecomments', 100)->nullable();
            $table->integer('status');
            $table->integer('flag')->default(1);
            $table->integer('parentid');
            $table->date('requisitiondate');
            $table->date('datesubmitted');
            $table->integer('submittedby')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('datesynchronized')->nullable();
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
