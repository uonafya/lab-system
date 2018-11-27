<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dr_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sample_id')->unsigned()->index();
            $table->string('drug_class', 50)->nullable(); 
            $table->tinyInteger('drug_class_id', 50)->nullable()->unsigned()->index(); 
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
        Schema::dropIfExists('dr_calls');
    }
}
