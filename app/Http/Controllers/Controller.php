<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function _columnBuilder($columns = null)
    {
        $column = '<tr>';
        if ($columns == null) {
            $column .= '<th><center>No Data available</center></th>';
        } else {
            foreach ($columns as $key => $value) {
                $column .= '<th>'.$value.'</th>';
            }
        }
        $column .= '</tr>';
        return $column;
    }

    public static function _getMonthQuarter($month=1, &$range=null){
        if ($month >0 && $month <4 ) {
            $quota=1;
            $range='JAN-MAR';
        }
        if ($month >3 && $month <7 ) {
            $quota=2;
            $range='APR-JUN';
        }
        if ($month >6 && $month <10 ) {
            $quota=3;
            $range='JUL-SEP';
        }
        if ($month >9 && $month <13) {
            $quota=4;
            $range='OCT-DEC';
        }
        return $quota;
    }

}
