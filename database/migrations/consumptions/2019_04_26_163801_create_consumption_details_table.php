<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsumptionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumption_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('consumption_id');
            $table->integer('kit_id');
            $table->integer('begining_balance')->default(0);
            $table->integer('positive_adjustment')->default(0)->comment("Received from other sources eg. other labs");
            $table->integer('wasted')->default(0);
            $table->integer('negative_adjustment')->default(0)->comment("Given to other labs");
            $table->integer('ending_balance')->default(0);
            $table->integer('request')->default(0);
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
        Schema::dropIfExists('consumption_details');
    }
}
