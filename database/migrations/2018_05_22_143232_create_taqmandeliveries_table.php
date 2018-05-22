<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaqmandeliveriesTable extends Migration
{
    public $main = ['qualkit','spexagent','ampinput','ampflapless','ampktips','ampwash','ktubes','consumables'];
    public $sub = ['received','damaged'];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taqmandeliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('testtype')->unsigned()->index();
            $table->tinyInteger('lab')->unsigned()->index();
            $table->tinyInteger('quarter')->unsigned()->index();
            $table->tinyInteger('year')->unsigned()->index();
            $table->tinyInteger('source')->unsigned()->nullable();
            $table->tinyInteger('labfrom')->unsigned()->nullable();
            $table->string('kitlotno', 12)->nullable();
            $table->date('expirydate')->nullable();
            foreach ($this->main as $key => $value) {
                foreach ($this->sub as $subkey => $subvalue) {
                    $table->integer($value.$subvalue)->unsigned()->nullable();
                }
            }
            $table->integer('receivedby')->unsigned()->index()->nullable();
            $table->date('datereceived')->nullable();
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->integer('enteredby')->unsigned()->index()->nullable();
            $table->date('dateentered')->nullable();
            $table->tinyInteger('flag')->unsigned()->default(1);
            $table->date('datesynchronized')->nullable();
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
        Schema::dropIfExists('taqmandeliveries');
    }
}
