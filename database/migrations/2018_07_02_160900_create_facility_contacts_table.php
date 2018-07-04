<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilityContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('facility_id')->unsigned()->index();
            
            $table->string('telephone', 20)->comment("Facility Telephone 1");
            $table->string('telephone2', 20)->comment("Facility Telephone 2");
            $table->string('fax', 30);
            $table->string('email', 30)->comment("Facility email Address");
            $table->string('PostalAddress', 40)->comment("Facility Contact Address");
            $table->string('contactperson', 30)->comment("Facility Contact Name");
            $table->string('contacttelephone', 20)->comment("Contact Person's Telephone 1");
            $table->string('contacttelephone2', 20)->comment("Contact Person's Telephone 2");
            $table->string('physicaladdress', 40);
            $table->string('G4Sbranchname', 100);
            $table->string('G4Slocation', 100);
            $table->string('G4Sphone1', 100);
            $table->string('G4Sphone2', 100);
            $table->string('G4Sphone3', 100);
            $table->string('G4Sfax', 100);

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
        Schema::dropIfExists('facility_contacts');
    }
}
