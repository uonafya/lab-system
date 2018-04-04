<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Sample;
use App\Facility;

class DashboardController extends Controller
{
    //

    public function index()
    {
   		
    	$lab_stats = (object) $this->lab_statistics();
    	$lab_tat_stats = (object) $this->lab_tat_statistics();
    	// dd($lab_tat_stats);
    	return view('dashboard.home', compact('lab_stats'), compact('lab_tat_stats'))->with('pageTitle', 'Lab Dashboard');
    }

    public function lab_statistics()
    {
    	return [
    		'testedSamples' => 	self::__getSamples()->whereRaw("YEAR(datetested) = ".Date('Y'))->count(),
	   		'rejectedSamples'=> 	self::__joinedToBatches()->where('samples.receivedstatus', '=', '2')
									->where('samples.repeatt', '=', '0')
									->whereRaw("YEAR(batches.datereceived) = ".Date('Y'))->count(),
			'failedSamples' => 	self::__getsampleResultByType(3),
			'inconclusive' 	=>	self::__getsampleResultByType(5),
			'redraws'		=>  self::__getsampleResultByType(3) + self::__getsampleResultByType(5),
			'positives' 	=> 	self::__getsampleResultByType(2),
			'negatives' 	=>	self::__getsampleResultByType(1),
			'receivedSamples'=>	self::__joinedToBatches()->whereRaw("YEAR(batches.datereceived) = ".Date('Y'))
														->whereRaw("((samples.parentid=0)||(samples.parentid IS NULL))")
														->count(),
			'smsPrinters' 	=>	Facility::where('smsprinter', '=', 1)
									->where('smsprinterphoneno', '<>', 0)
									->where('lab', '=', Auth()->user()->lab_id)->count()
			];

		
    }

    public function lab_tat_statistics()
    {
        return [
        	'tat1' => self::__getTAT(1),
            'tat2' => self::__getTAT(2),
            'tat3' => self::__getTAT(3),
            'tat4' => self::__getTAT(4),
            'tat5' => self::__getTAT(5)
        ];
    }

    public static function __getsampleResultByType($type = null)
    {
    	if ($type == null || !is_int($type))
    		return 0;

    	return 	self::__getSamples()
						->where('result', '=', $type)
						->where('repeatt', '=', '0')
						->whereRaw("YEAR(datetested) = ".Date('Y'))->count();
    	
    }

    public static function __joinedToBatches()
    {
    	return DB::table('samples')
		   			->join('batches', 'batches.id', '=', 'samples.batch_id')
		   			->where('samples.flag', '=', 1);
    }


    public static function __getSamples()
    {
    	return Sample::with('batch')->where('flag', '=', 1);
    }

    public static function __getTAT($tat = null)
    {
    	if ($tat == null || !is_int($tat))
    		return 0;

    	if ($tat == 1) {
    		$d1 = "samples.datecollected";
    		$d2 = "batches.datereceived";
    	} else if ($tat == 2) {
    		$d1 = "batches.datereceived";
            $d2 = "samples.datetested";
    	} else if ($tat == 3) {
            $d1 = "samples.datetested";
            $d2 = "batches.datedispatched";
        } else if ($tat == 4) {
            $d1 = "samples.datecollected";
            $d2 = "batches.datedispatched";
        } else if ($tat == 5) {
            $d1 = "batches.datereceived";
            $d2 = "batches.datedispatched";
        } else {
            return 0;
        }
    	
    	return  self::__getActualTATDays(DB::table('samples')
    					->select("$d1 as d1", "$d2 as d2", DB::RAW("TIMESTAMPDIFF(DAY,$d1,$d2) as daysdiff"))
    					->join('batches', 'batches.id', '=', 'samples.batch_id')
    					->where($d2, '<>', '0000-00-00')
    					->where($d2, '<>', '1970-01-01')
    					->where($d1, '<>', '0000-00-00')
    					->where($d1, '<>', '1970-01-01')
    					->where($d1, '<=', $d2)
                        ->whereRaw("YEAR(samples.datetested) = ".Date('Y'))
    					->where('repeatt', '=', 0)
                        ->where('flag', '=', 1)
    					->get());
    }

    public static function __getActualTATDays($data = null)
    {
        $sumdates=0;
        if ($data == null)
            return null;

        $numsamples = $data->count();
        if ($numsamples > 0) {
            foreach ($data as $key => $value) {
                $secondDate = date("d-m-Y",strtotime($value->d2));
                $firstDate = date("d-m-Y",strtotime($value->d1));
                $workingdays = self::__getWorkingDays($firstDate,$secondDate,0) ;
                $month = Date('m');

                if ($month > 0) {
                    $totalholidays = self::__getTotalHolidaysinMonth($month);
                }
                else {
                    $totalholidays=0;
                }

                $totaldays =$workingdays -$totalholidays;
                if ($totaldays < 0) {
                    $totaldays=1;
                }
                else {
                    $totaldays=$totaldays;
                }
                $sumdates=$sumdates+$totaldays;
            }
            $ave=floor(($sumdates/$numsamples));
        
            if ($ave==0) { $ave=1; }
            elseif ($ave < 0) { $ave=1; }
            else { $ave=$ave; }

            return $ave;
        } else {
            return 0;
        }
    }

    public static function __getWorkingDays($startDate,$endDate,$holidays){
        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;

        $no_full_weeks = floor($days / 7);

        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N",strtotime($startDate));

        $the_last_day_of_week = date("N",strtotime($endDate));
        
        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week){
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }

        else{
            if ($the_first_day_of_week <= 6) {
            //In the case when the interval falls in two weeks, there will be a Sunday for sure
                $no_remaining_days--;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
    //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
       $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
          $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
    /*    foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if (strtotime($startDate) <= $time_stamp && $time_stamp <= strtotime($endDate) && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }*/

        return $workingDays;
    } 

    public static function __getTotalHolidaysinMonth($month)
    {
        if ($month==0) { $totalholidays=10; }
        if ($month==1) { $totalholidays=1; }
        else if ($month==4) { $totalholidays=2; }
        else if ($month==5) { $totalholidays=1; }
        else if ($month==6) { $totalholidays=1; }
        else if ($month==8) { $totalholidays=1; }
        else if ($month==10) { $totalholidays=1; }
        else if ($month==12) { $totalholidays=3; }
        else if ($month=="") { $totalholidays=10; }
        else { $totalholidays=0; }

        return $totalholidays;
    }
}
