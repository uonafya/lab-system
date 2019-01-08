<?php

namespace App;
use Excel;
use DB;
use App\Facilitys;

class Random
{

	public static function facilitys()
	{
		self::alter_facilitys();
		self::poc_sites();
		self::mlab_sites();
	}

	public static function alter_facilitys()
	{
		DB::statement('ALTER TABLE facilitys ADD COLUMN `poc` TINYINT UNSIGNED DEFAULT 0 after latitude;');
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

	public static function mlab_sites()
	{
		ini_set("memory_limit", "-1");
        config(['excel.import.heading' => true]);
		$path = public_path('mlab_facilities.csv');
		$data = Excel::load($path, function($reader){

		})->get();

		foreach ($data as $row) {
			Facility::where(['facilitycode' => $row->code])->update(['smsprinter' => 1]);
		}
	} 

}
