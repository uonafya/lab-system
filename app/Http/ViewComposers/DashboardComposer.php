<?php
namespace App\Http\ViewComposers;

use DB;
use Illuminate\View\View;
use App\Batch;
use App\Viralbatch;
use App\Facility;
use App\SampleView;
use App\ViralsampleView;
use App\Worksheet;
use App\Viralworksheet;
/**
* 
*/
class DashboardComposer
{
	
	public $DashboardData = [];
	public $tasks = [];
    public $user = [];
	function __construct()
	{
		$this->DashboardData['pendingSamples'] = self::pendingSamplesAwaitingTesting();
        $this->DashboardData['pendingSamplesOverTen'] = self::pendingSamplesAwaitingTesting(true);
		$this->DashboardData['batchesForApproval'] = self::siteBatchesAwaitingApproval();
        $this->DashboardData['batchesNotReceived'] = self::batchesMarkedNotReceived();
		$this->DashboardData['batchesForDispatch'] = self::batchCompleteAwaitingDispatch();
		$this->DashboardData['samplesForRepeat'] = self::samplesAwaitingRepeat();
		$this->DashboardData['rejectedForDispatch'] = self::rejectedSamplesAwaitingDispatch();
        $this->DashboardData['resultsForUpdate'] = self::resultsAwaitingpdate();
	}

	/**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // dd($this->DashboardData);
        $view->with('widgets',$this->DashboardData);

    }

    public function tasks(View $view)
    {
    	$this->tasks['labname'] = DB::table('labs')->select('name')->where('id', '=', Auth()->user()->lab_id)->get();
    	$this->tasks['facilityServed'] = Facility::selectRaw('COUNT(*) as total')->where('Flag', '=', 1)->where('lab', '=', Auth()->user()->lab_id)->get()->first()->total;
    	$this->tasks['facilitieswithSmsPrinters'] = Facility::selectRaw('COUNT(*) as total')->where('Flag', '=', 1)->where('lab', '=', Auth()->user()->lab_id)->where('smsprinter', '<>', '')->get()->first()->total;
    	$this->tasks['facilitiesWithoutEmails'] = Facility::selectRaw('COUNT(*) as total')
                                                ->where('Flag', '=', 1)
    											->where('lab', '=', Auth()->user()->lab_id)
    											->whereRaw("((email = '' and ContactEmail ='') or (email = '' and ContactEmail is null) or (email is null and ContactEmail ='') or ((email is null and ContactEmail is null)))")
    											->get()->first()->total;
    	$this->tasks['facilitiesWithoutG4s'] = Facility::selectRaw('COUNT(*) as total')
                                                    ->where('Flag', '=', 1)->where('lab', '=', Auth()->user()->lab_id)
    												->where('G4Sbranchname', '=', '')->where('G4Slocation', '=', '')
    												->get()->first()->total;
    	
    	$view->with('tasks', $this->tasks);
    }

	public function sidenav(View $view)
	{
		
	}

    public function users(View $view)
    {
        if(!empty(auth()->user()->facility_id)) {
            foreach(Facility::where('id', auth()->user()->facility_id)->get() as $key => $value) {
                $this->user = $value;
            }
        }
        
        $view->with('user', $this->user);
    }

	public function pendingSamplesAwaitingTesting($over = false)
	{
        if (session('testingSystem') == 'Viralload') {
            if ($over == true) {
                $model = ViralsampleView::selectRaw('COUNT(id) as aggregate')->whereNull('worksheet_id')
                                ->whereRaw("datediff(datereceived, datetested) > 10")
                                ->get();
            } else {
                $sampletype = ['plasma'=>[1,1],'EDTA'=>[2,2],'DBS'=>[3,4],'all'=>[1,4]];
                foreach ($sampletype as $key => $value) {
                    $model[$key] = ViralsampleView::selectRaw('COUNT(id) as aggregate')->whereNotIn('receivedstatus', ['0', '2', '4'])
                        ->whereBetween('sampletype', [$value[0], $value[1]])
                        ->whereNull('worksheet_id')
                        ->where('datereceived', '>', '2016-12-31')
                        ->whereRaw("(result is null or result = 0 or result != 'Collect New Sample')")
                        ->where('input_complete', '=', '1')
                        ->where('flag', '=', '1')->get(); 
                }
            }
        } else {
            if ($over == true) {
                $model = SampleView::selectRaw('COUNT(id) as aggregate')->whereNull('worksheet_id')
                                ->whereRaw("datediff(datereceived, datetested) > 10")
                                ->get();
            } else {
                $model = SampleView::selectRaw('COUNT(id) as aggregate')->whereNull('worksheet_id')
                    ->where('datereceived', '>', '2014-12-31')
                    ->whereNotIn('receivedstatus', ['0', '2', '4'])
                    ->whereRaw("(result is null or result = 0)")
                    ->where('input_complete', '1')
                    ->where('flag', '1')->get();
            }
        }
        
        return $model[0]->aggregate;
	}

	public function siteBatchesAwaitingApproval()
	{
        if (session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::selectRaw('COUNT(ID) as totalsamples')
                        ->where('lab_id', '=', Auth()->user()->lab_id)
                        ->where('flag', '=', '1')
                        ->where('repeatt', '=', '0')
                        ->whereNull('receivedstatus')
                        ->where('site_entry', '=', '1');
        } else {
            $model = SampleView::selectRaw('COUNT(ID) as totalsamples')
                    ->where('lab_id', '=', Auth()->user()->lab_id)
                    ->where('flag', '=', '1')
                    ->where('repeatt', '=', '0')
                    ->whereNull('receivedstatus')
                    ->where('site_entry', '=', '1');
        }
        return $model->get()->first()->totalsamples ?? 0;
	}

	public function batchCompleteAwaitingDispatch()
	{
        if (session('testingSystem') == 'Viralload') {
            $model = Viralbatch::class;
        } else {
            $model = Batch::class;
        }
        return $model::selectRaw('COUNT(*) as total')->where('lab_id', '=', Auth()->user()->lab_id)->where('batch_complete', '=', '2')->get()->first()->total;
	}

	public function samplesAwaitingRepeat()
	{
        if(session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::selectRaw('COUNT(*) as total')->whereBetween('sampletype', [1, 5])
                        ->whereNotIn('receivedstatus', ['0', '2'])
                        ->whereNull('worksheet_id')
                        ->where(DB::raw('YEAR(datereceived)'), '>', '2015')
                        ->where('parentid', '>', 0)
                        ->whereRaw("result is null or result = 0 or result != 'Collect New Sample'")
                        ->where('input_complete', '=', '1')
                        ->where('flag', '=', '1');
        } else {
            $model = SampleView::selectRaw('COUNT(*) as total')->whereNull('worksheet_id')
                    ->whereNotIn('receivedstatus', ['0', '2'])
                    ->where(function ($query) {
                        $query->whereNull('result')
                              ->orWhere('result', '=', 0);
                    })
                    // ->where(DB::raw(('samples.result is null or samples.result = 0')))
                    ->where('flag', '=', '1')
                    ->where('parentid', '>', '0');
        }
		return $model->get()->first()->total;
	}

	public function rejectedSamplesAwaitingDispatch()
	{
        $year = Date('Y')-3;
        if (session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::selectRaw('count(*) as rejectfordispatch')
                        ->where('receivedstatus', '=', 2)
                        ->where('flag', '=', 1)
                        ->whereYear('datereceived', '>', $year)
                        ->whereNotNull('datereceived')
                        ->where('datedispatched', '=', '')
                        ->orWhere('datedispatched', '=', '0000-00-00')
                        ->orWhere('datedispatched', '=', '1970-01-01')
                        ->orWhereNotNull('datedispatched');
        } else {
            $model = SampleView::selectRaw('count(*) as rejectfordispatch')
                        ->where('receivedstatus', '=', 2)
                        ->whereNotNull('datereceived')
                        ->whereYear('datereceived', '>', $year);
        }
        
		return $model->get()->first()->rejectedForDispatch ?? 0;
	}

    public function batchesMarkedNotReceived()
    {
        $model = 0;
        if (session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::selectRaw('count(distinct batch_id) as total')
                        ->where('receivedstatus', '=', '4')
                        ->orWhereNull('receivedstatus')->get()->first();
        } else {
            # code...
        }
        
        return $model->total ?? 0;
    }

    public function resultsAwaitingpdate()
    {
        if (session('testingSystem') == 'Viralload') {
            $model = Viralworksheet::with(['creator']);
        } else {
            $model = Worksheet::with(['creator']);
        }

        return $model->selectRaw('count(*) as total')->where('status_id', '=', '1')->get()->first()->total ?? 0;
    }
}

?>