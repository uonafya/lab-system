<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAllocationsAddMachinesAndTestType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allocations', function(Blueprint $table){
            // $table->integer('machine_id')->after('national_id');
            // $table->dropColumn('kit_id');
            // $table->dropColumn('allocated');
            // $table->dropColumn('issued');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}