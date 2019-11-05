<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDeliveriesTableRebuild extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('deliveries');
        Schema::create('deliveries', function(Blueprint $table){
            $table->bigIncrements();
            $table->bigInteger('national_id')->nullable();
            $table->tinyInteger('quarter')->comment('This is the quarter in which the delivery was done');
            $table->integer('year')->comment('This is the quarter in which the delivery was done');
            $table->tinyInteger('type')->nullable()->comment("Indicates if this is an EID, VL, Consumable delivery");
            $table->tinyInteger('source')->default('3')->comment('Default source is KEMSA');
            $table->tinyInteger('labfrom')->nullable()->comment("If Source is lab");
            $table->tinyInteger('lab_id');
            $table->bigInteger('receivedby');
            $table->dete('datereceived');
            $table->bigInteger('enteredby');
            $table->date('dateentered');
            $table->tinyInteger('synched')->default(0);
            $table->dateTime('datesynched')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('deliveries');
    }
}
