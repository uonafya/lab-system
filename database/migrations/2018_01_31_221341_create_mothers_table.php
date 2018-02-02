<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMothersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mothers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ccc_no')->nullable()->index();
            $table->integer('fcode')->unsigned()->index()->nullable();
            $table->integer('facility_id')->unsigned()->index();
            $table->integer('entry_point')->unsigned()->index();
            $table->integer('hiv_status')->unsigned();
            $table->integer('age')->unsigned()->nullable();
            $table->boolean('synched')->default(false);
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
        Schema::dropIfExists('mothers');
    }
}
