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
    	// dd($lab_stats);
    	return view('dashboard.home', compact('lab_stats'))->with('pageTitle', 'Lab Dashboard');
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
    	$tat1 = self::__getTAT(1);
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
// datecollected as d1,datereceived as d2, 
// TIMESTAMPDIFF(DAY,`datecollected`,`datereceived`) as `daysdiff`
    	if ($tat == 1) {
    		$d1 = "samples.datecollected";
    		$d2 = "batches.datereceived";
    	} else {
    		# code...
    	}
    	
  //   	WHERE datereceived !='0000-00-00' 
		// AND datereceived !='1970-01-01' 
		// AND datecollected !='0000-00-00' 
		// AND datecollected !='1970-01-01' 
		// AND datecollected <= datereceived 
		// AND YEAR(datetested)='2017' 
		// AND repeatt=0
		// AND Flag=1
    	$dates = DB::table('samples')
    					->select("$d1 as d1", "$d2 as d2", DB::RAW("TIMESTAMPDIFF(DAY,$d1,$d2) as daysdiff"))
    					->join('batches', 'batches.id', '=', 'samples.batch_id')
    					->where()
    					// ->where()
    					// ->where()
    					// ->where()
    					// ->where()
    					// ->where()
    					// ->where()
    					->get();
    	dd($dates);
    }
}
