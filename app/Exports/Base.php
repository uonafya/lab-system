<?php

namespace App\Exports;

use \Maatwebsite\Excel\Sheet;

class Base
{
	use RequestFilters;

	public function __construct()
	{
		Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
		    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
		});		
	}

}
