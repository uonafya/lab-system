<?php
namespace App\Http\ViewComposers;

use DB;
use Illuminate\View\View;
use App\Batch;

/**
* 
*/
class DashboardComposer
{
	
	public $DashboardData = [];
	function __construct()
	{
		$this->DashboardData['pendingSamples'] = sizeof(self::pendingSamplesAwaitingTesting());
		$this->DashboardData['batchesForApproval'] = self::siteBatchesAwaitingApproval();
		$this->DashboardData['batchesForDispatch'] = self::batchCompleteAwaitingDispatch();
		$this->DashboardData['samplesForRepeat'] = sizeof(self::samplesAwaitingRepeat());
		$this->DashboardData['rejectedForDispatch'] = self::rejectedSamplesAwaitingDispatch();
	}

	/**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
    	$view->with('widgets',$this->DashboardData);
    }

	public function sidenav(View $view)
	{
		
	}

	public function pendingSamplesAwaitingTesting()
	{
		return DB::table('samples')
                    ->select('samples.id','patients.patient','samples.parentid','batches.datereceived', DB::raw('IF(samples.patient_id > 0 OR samples.parentid IS NULL, 0, 1) AS isnull'))
                    ->leftJoin('batches', 'batches.id', '=', 'samples.batch_id')
                    ->leftJoin('patients', 'patients.id', '=', 'samples.patient_id')
                    ->where('samples.inworksheet', '=', 0)
                    ->where(DB::raw('YEAR(batches.datereceived)'), '>', '2014')
                    ->where('samples.receivedstatus', '<>', '0')
                    ->where('samples.receivedstatus', '<>', '2')
                    ->where(DB::raw('samples.result is null or samples.result = 0'))
                    ->where('batches.input_complete', '=', '1')
                    ->where('samples.flag', '=', '1')
                    ->orderBy('isnull', 'ASC')
                    ->orderBy('batches.datereceived', 'ASC')
                    ->orderBy('samples.parentid', 'ASC')
                    ->orderBy('samples.id', 'ASC')
                    ->get();
	}

	public function siteBatchesAwaitingApproval()
	{
		return DB::table('samples')
                    ->select(DB::raw('COUNT(samples.ID) as totalsamples'))
                    ->leftJoin('batches', 'batches.id', '=', 'samples.batch_id')
                    ->where('batches.lab_id', '=', Auth()->user()->lab_id)
                    ->where('samples.flag', '=', '1')
                    ->where('samples.repeatt', '=', '0')
                    ->where('samples.receivedstatus', '=', '0')
                    ->where('batches.site_entry', '=', '1')
                    ->get();
	}

	public function batchCompleteAwaitingDispatch()
	{
		return Batch::where('lab_id', '=', Auth()->user()->lab_id)
						->where('batch_complete', '=', '2')
						->count();
	}

	public function samplesAwaitingRepeat()
	{
		return DB::table('samples')
                    ->select('samples.id','patient_id','batches.datereceived','spots', 'datecollected','receivedstatus','batches.facility_id','samples.worksheet_id', DB::raw('IF(samples.patient_id > 0 OR samples.parentid IS NULL, 0, 1) AS isnull'))
                    ->join('batches', 'batches.id', '=', 'samples.batch_id')
                    ->join('patients', 'patients.id', '=', 'samples.patient_id')
                    ->where('samples.inworksheet', '=', 0)
                    ->where('samples.receivedstatus', '<>', '0')
                    ->where('samples.receivedstatus', '<>', '2')
                    ->where(function ($query) {
					    $query->whereNull('samples.result')
					          ->orWhere('samples.result', '=', 0);
					})
                    // ->where(DB::raw(('samples.result is null or samples.result = 0')))
                    ->where('samples.flag', '=', '1')
                    ->where('samples.parentid', '>', '0')
                    ->orderBy('isnull', 'ASC')
                    ->orderBy('batches.datereceived', 'ASC')
                    ->orderBy('samples.parentid', 'ASC')
                    ->orderBy('samples.id', 'ASC')
                    ->get();
	}

	public function rejectedSamplesAwaitingDispatch()
	{
		$year = Date('Y')-3;
		return DB::table('samples')
					->select(DB::raw('count(*) as rejectfordispatch'))
					->join('batches', 'batches.id', '=', 'samples.batch_id')
					->where('samples.receivedstatus', '=', 2)
					->whereNotNull('batches.datereceived')
					->where(DB::raw('YEAR(batches.datereceived) > '.$year))
					->get();
	}
}

?>