<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UlizaPage extends Model
{
	public $timestamps = false;

    public function save_raw($page_content, $attr=null)
    {
    	$file = $attr ?? $this->link;
    	if(!is_dir(storage_path('app/uliza/pages'))) mkdir(storage_path('app/uliza/pages'), 0777, true);

    	$filename = storage_path('app/uliza/pages') . '/' . $file . '.html';

    	file_put_contents($filename, $page_content);
    }

    public function get_raw($attr=null)
    {
    	$file = $attr ?? $this->link;
    	if(!is_dir(storage_path('app/uliza/pages'))) mkdir(storage_path('app/uliza/pages'), 0777, true);

    	$filename = storage_path('app/uliza/pages') . '/' . $file . '.html';
    	if(!file_exists($filename)) return null;
    	return file_get_contents($filename);
    }

    // 

    public function seed()
    {
    	$this->create_table();
    	$this->create_rows();
    }

    public function create_table()
    {
    	\DB::statement('DROP TABLE IF EXISTS uliza_pages');
    	\DB::statement("
    		CREATE TABLE uliza_pages(
    			id tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    			link varchar(30) UNIQUE,
    			title varchar(30) DEFAULT NULL,
                PRIMARY KEY (`id`)
    		)
    	");

        \DB::statement('DROP TABLE IF EXISTS uliza_reasons');
        \DB::statement("
            CREATE TABLE uliza_reasons(
                id tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(80),
                PRIMARY KEY (`id`)
            )
        ");
    }

    public function create_rows(){
    	\DB::table('uliza_pages')->insert([
    		['link' => 'home', 'title' => 'Executive Summary'],
    		['link' => 'uliza', 'title' => 'Uliza-NASCOP'],
    		['link' => 'ushauri', 'title' => 'Ushauri'],
    		['link' => 'trainsmart', 'title' => 'TrainSMART'],
    		['link' => 'echo', 'title' => 'Echo'],
    		['link' => 'faqs', 'title' => 'Frequently Asked Questions'],
    		['link' => 'contactus', 'title' => 'Contact Us'],

    		['link' => 'home2', 'title' => 'Functions'],
    	]);

        \DB::table('uliza_reasons')->insert([
            ['name' => 'Adverse drug reaction'],
            ['name' => 'Private cross-over patient'],
            ['name' => 'Single TDF out of stock'],
            ['name' => 'Drug interactions'],
            ['name' => 'Second line failure'],
            ['name' => 'Study cross-over patient'],
            ['name' => 'First line failure'],
            ['name' => 'Single AZT out of stock'],
            ['name' => 'Other (Please Specify)'],
        ]);
    }

}
