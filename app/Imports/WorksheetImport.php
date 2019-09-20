<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class WorksheetImport implements ToCollection
{
	protected $worksheet;

	public function __construct()
	{

	}

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
    	foreach ($collection as $key => $row) {
    		# code...
    	}
    }
}
