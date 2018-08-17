<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dr_samples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index();

            $table->tinyInteger('prev_prophylaxis')->unsigned()->nullable();
            $table->tinyInteger('prophylaxis')->unsigned()->nullable();
            $table->tinyInteger('receivedstatus')->unsigned()->nullable(); 
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();


            $table->tinyInteger('sample_type')->unsigned()->nullable();
            $table->string('clinication_id', 50)->nullable();

            $table->boolean('has_opportunistic_infections')->default(0);
            $table->string('opportunistic_infections', 100)->nullable();

            $table->boolean('has_tb')->default(0);
            $table->tinyInteger('tb_treatment_phase_id')->unsigned()->nullable();

            $table->boolean('has_arv_toxicity')->default(0);
            $table->string('arv_toxicities', 50)->nullable();

            $table->string('cd4_result', 50)->nullable();

            // Adherence
            $table->boolean('has_missed_pills')->default(0);
            $table->smallInteger('missed_pills')->unsigned()->nullable();
            $table->boolean('has_missed_visits')->default(0);
            $table->smallInteger('missed_visits')->unsigned()->nullable();
            $table->boolean('has_missed_pills_because_missed_visits')->default(0);

            $table->string('other_medications', 50)->nullable();



            // startartdate
            $table->date('date_prev_regimen')->nullable(); 
            $table->date('date_current_regimen')->nullable(); 

            $table->integer('worksheet_id')->nullable()->unsigned()->index();

            $table->date('datecollected')->nullable();
            $table->date('datereceived')->nullable();
            $table->date('datetested')->nullable();
            $table->date('datedispatched')->nullable();

            $table->tinyInteger('dr_reason_id')->unsigned()->index();

            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('received_by')->unsigned()->nullable();
            
            $table->tinyInteger('synched')->default(0)->nullable();
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
        Schema::dropIfExists('dr_samples');
    }
}
