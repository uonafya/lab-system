<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewTaqmanProcurements extends Migration
{
    public $main = ['ending','wasted','issued','request','pos'];
    public $sub = ['qualkit','spexagent','ampinput','ampflapless','ampktips','ampwash','ktubes','consumables'];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taqmanprocurements', function (Blueprint $table) {
            // $table->softDeletes()->after('datesynched');
        });
        // Schema::create('taqmanprocurements', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->tinyInteger('month')->unsigned();
        //     $table->integer('year')->unsigned();
        //     $table->tinyInteger('testtype')->unsigned();
        //     $table->integer('received')->unsigned()->default(0)->nullable();
        //     $table->integer('tests')->unsigned()->default(0)->nullable();
        //     foreach ($this->main as $key => $value) {
        //         foreach ($this->sub as $subkey => $subvalue) {
        //             $table->integer($value.$subvalue)->unsigned()->default(0)->nullable();
        //         }
        //     }
        //     $table->date('datesubmitted')->nullable();
        //     $table->integer('submittedBy')->unsigned()->nullable();
        //     $table->tinyInteger('lab_id')->unsigned()->nullable();
        //     // $table->tinyInteger('synchronized')->unsigned()->nullable();
        //     // $table->date('datesynchronized');
        //     $table->string('comments', 50)->nullable();
        //     $table->string('issuedcomments', 100)->nullable();
        //     $table->boolean('approve')->default(0);
        //     $table->string('disapprovereason', 100)->nullable();
        //     $table->tinyInteger('synched')->default(0);
        //     $table->date('datesynched')->nullable();
        //     $table->timestamps();

        //     $table->index(['year', 'month'], 'year_month');
        // });
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
