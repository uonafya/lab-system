<?php
namespace App\Http\ViewComposers;

use DB;
use Illuminate\View\View;
use App\Batch;
use App\Viralbatch;
use App\Facility;
use App\ViralsampleView;
/**
* 
*/
class DashboardComposer
{
	
	public $DashboardData = [];
	public $tasks = [];
	function __construct()
	{
		$this->DashboardData['pendingSamples'] = self::pendingSamplesAwaitingTesting();
		$this->DashboardData['batchesForApproval'] = self::siteBatchesAwaitingApproval();
        $this->DashboardData['batchesNotReceived'] = self::batchesMarkedNotReceived();
		$this->DashboardData['batchesForDispatch'] = self::batchCompleteAwaitingDispatch();
		$this->DashboardData['samplesForRepeat'] = self::samplesAwaitingRepeat();
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

    public function tasks(View $view)
    {
    	$this->tasks['labname'] = DB::table('labs')->select('name')->where('id', '=', Auth()->user()->lab_id)->get();
    	$this->tasks['facilityServed'] = Facility::where('Flag', '=', 1)->where('lab', '=', Auth()->user()->lab_id)->count();
    	$this->tasks['facilitieswithSmsPrinters'] = Facility::where('Flag', '=', 1)->where('lab', '=', Auth()->user()->lab_id)->where('smsprinter', '<>', '')->count();
    	$this->tasks['facilitiesWithoutEmails'] = Facility::where('Flag', '=', 1)
    											->where('lab', '=', Auth()->user()->lab_id)
    											->whereRaw("((email = '' and ContactEmail ='') or (email = '' and ContactEmail is null) or (email is null and ContactEmail ='') or ((email is null and ContactEmail is null)))")
    											->count();
    	$this->tasks['facilitiesWithoutG4s'] = Facility::where('Flag', '=', 1)->where('lab', '=', Auth()->user()->lab_id)
    													->where('G4Sbranchname', '=', '')->where('G4Slocation', '=', '')
    													->count();
    	
    	$view->with('tasks', $this->tasks);
    }

	public function sidenav(View $view)
	{
		
	}

	public function pendingSamplesAwaitingTesting()
	{
        if (session('testingSystem') == 'Viralload') {
            $sampletype = ['plasma'=>[1,1],'EDTA'=>[2,2],'DBS'=>[3,4],'all'=>[1,4]];
            foreach ($sampletype as $key => $value) {
                $model[$key] = ViralsampleView::where('receivedstatus', '<>', '0')
                    ->where('receivedstatus', '<>', '2')
                    ->where('receivedstatus', '<>', '4')
                    ->whereBetween('sampletype', [$value[0], $value[1]])
                    ->whereNull('worksheet_id')
                    ->where(DB::raw('YEAR(datereceived)'), '>', '2016')
                    ->whereRaw("result is null or result = 0 or result != 'Collect New Sample'")
                    ->where('input_complete', '=', '1')
                    ->where('flag', '=', '1')->count();
            }
        } else {
            $model = DB::table('samples')
                    ->select('samples.id','patients.patient','samples.parentid','batches.datereceived', DB::raw('IF(samples.patient_id > 0 OR samples.parentid IS NULL, 0, 1) AS isnull'))
                    ->leftJoin('batches', 'batches.id', '=', 'samples.batch_id')
                    ->leftJoin('patients', 'patients.id', '=', 'samples.patient_id')
                    ->whereNull('samples.worksheet_id')
                    ->where(DB::raw('YEAR(batches.datereceived)'), '>', '2014')
                    ->where('samples.receivedstatus', '<>', '0')
                    ->where('samples.receivedstatus', '<>', '2')
                    ->where(DB::raw('samples.result is null or samples.result = 0'))
                    ->where('batches.input_complete', '=', '1')
                    ->where('samples.flag', '=', '1')
                    ->orderBy('isnull', 'ASC')
                    ->orderBy('batches.datereceived', 'ASC')
                    ->orderBy('samples.parentid', 'ASC')
                    ->orderBy('samples.id', 'ASC')->count();
        }
        
        return $model;
	}

	public function siteBatchesAwaitingApproval()
	{
        if (session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::select(DB::raw('COUNT(ID) as totalsamples'))
                        ->where('lab_id', '=', Auth()->user()->lab_id)
                        ->where('flag', '=', '1')
                        ->where('repeatt', '=', '0')
                        ->where('receivedstatus', '=', '0')
                        ->where('site_entry', '=', '1');
        } else {
            $model = DB::table('samples')
                    ->select(DB::raw('COUNT(samples.ID) as totalsamples'))
                    ->leftJoin('batches', 'batches.id', '=', 'samples.batch_id')
                    ->where('batches.lab_id', '=', Auth()->user()->lab_id)
                    ->where('samples.flag', '=', '1')
                    ->where('samples.repeatt', '=', '0')
                    ->where('samples.receivedstatus', '=', '0')
                    ->where('batches.site_entry', '=', '1');
        }
        return $model->get();
	}

	public function batchCompleteAwaitingDispatch()
	{
        if (session('testingSystem') == 'Viralload') {
            $model = Viralbatch::class;
        } else {
            $model = Batch::class;
        }
        return $model::where('lab_id', '=', Auth()->user()->lab_id)->where('batch_complete', '=', '2')->count();
	}

	public function samplesAwaitingRepeat()
	{
        if(session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::where('receivedstatus', '<>', '0')
                        ->where('receivedstatus', '<>', '2')
                        ->whereBetween('sampletype', [1, 5])
                        ->whereNull('worksheet_id')
                        ->where(DB::raw('YEAR(datereceived)'), '>', '2015')
                        ->where('parentid', '>', 0)
                        ->whereRaw("result is null or result = 0 or result != 'Collect New Sample'")
                        ->where('input_complete', '=', '1')
                        ->where('flag', '=', '1');
        } else {
            $model = DB::table('samples')
                    ->select('samples.id','patient_id','batches.datereceived','spots', 'datecollected','receivedstatus','batches.facility_id','samples.worksheet_id', DB::raw('IF(samples.patient_id > 0 OR samples.parentid IS NULL, 0, 1) AS isnull'))
                    ->join('batches', 'batches.id', '=', 'samples.batch_id')
                    ->join('patients', 'patients.id', '=', 'samples.patient_id')
                    ->whereNull('samples.worksheet_id')
                    ->where('samples.receivedstatus', '<>', '0')
                    ->where('samples.receivedstatus', '<>', '2')
                    ->where(function ($query) {
                        $query->whereNull('samples.result')
                              ->orWhere('samples.result', '=', 0);
                    })
                    // ->where(DB::raw(('samples.result is null or samples.result = 0')))
                    ->where('samples.flag', '=', '1')
                    ->where('samples.parentid', '>', '0');
        }
		return $model->count();
	}

	public function rejectedSamplesAwaitingDispatch()
	{
        $year = Date('Y')-3;
        if (session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::where('receivedstatus', '=', 2)
                        ->where('flag', '=', 1)
                        ->whereRaw("YEAR(datereceived) > $year")
                        ->whereNotNull('datereceived')
                        ->where('datedispatched', '=', '')
                        ->orWhere('datedispatched', '=', '0000-00-00')
                        ->orWhere('datedispatched', '=', '1970-01-01')
                        ->orWhereNotNull('datedispatched')->count();
        } else {
            $model = DB::table('samples')
                        ->select(DB::raw('count(*) as rejectfordispatch'))
                        ->join('batches', 'batches.id', '=', 'samples.batch_id')
                        ->where('samples.receivedstatus', '=', 2)
                        ->whereNotNull('batches.datereceived')
                        ->where(DB::raw('YEAR(batches.datereceived) > '.$year))
                        ->get();
        }
        
		return $model;
	}

    public function batchesMarkedNotReceived()
    {
        $model = 0;
        if (session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::select(DB::raw('distinct(batch_id)'))
                        ->where('receivedstatus', '=', '4')
                        ->orWhereNull('receivedstatus')->count();
        } else {
            # code...
        }
        
        return $model;
    }
}

?>