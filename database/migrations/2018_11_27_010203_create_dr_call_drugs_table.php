<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrCallDrugsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dr_call_drugs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('call_id')->unsigned()->index();
            $table->string('short_name', 20)->nullable(); 
            $table->tinyInteger('short_name_id', 50)->nullable()->unsigned()->index();
            $table->string('call', 10)->nullable(); 
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
        Schema::dropIfExists('dr_call_drugs');
    }
}
