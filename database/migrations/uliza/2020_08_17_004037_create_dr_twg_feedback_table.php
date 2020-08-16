<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrTwgFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dr_twg_feedback', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dr_clinical_form_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->date('review_date');
            $table->string('casesummary');
            $table->string('observationsofsummary');
            $table->tinyInteger('diagnosis');
            $table->string('diagnosis_other');
            $table->text('supportivemanagement');
            $table->text('definativemanagement');
            $table->text('additionalinfo');
            $table->text('nascop_comments');
            $table->tinyInteger('recommendation_id');
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
        Schema::dropIfExists('dr_twg_feedback');
    }
}
