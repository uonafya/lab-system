<?php

namespace App;
use Excel;
use DB;
use App\Facilitys;

class Random
{

	public static function alter_facilitys()
	{
		DB::statement('ALTER TABLE facilitys ADD `poc` TINYINTEGER UNSIGNED DEFAULT 0 after latitude;');
		Facility::where('id', '>', 0)->update(['smsprinter' => 0]);
	}

	public static function poc_sites()
	{
		ini_set("memory_limit", "-1");
        config(['excel.import.heading' => true]);
		$path = public_path('poc_hubs_list.csv');
		$data = Excel::load($path, function($reader){

		})->get();

		foreach ($data as $row) {
			Facility::where(['facilitycode' => $row->code])->update(['poc' => 1]);
		}
	} 

}
