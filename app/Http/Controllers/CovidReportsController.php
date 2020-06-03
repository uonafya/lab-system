<?php

namespace App\Http\Controllers;

use App\CovidSampleView;
use App\CovidSample;
use App\Lab;
use Carbon\Carbon;
use Excel;
use DB;
use Illuminate\Http\Request;

class CovidReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('covid_allowed');   
    }
    
    private $quarters = ['Q1' => '1,2,3', 'Q2' => '4,5,6', 'Q3' => '7,8,9', 'Q4' => '10,11,12'];
	public function index()
	{
		return view('reports.covidreports');
	}

	public function generate(Request $request)
	{
		// Get the dates
		$date = Carbon::parse($request->input('date_filter'))->format('Y-m-d');
		
		// Get the data from the database
		$today_data = $this->get_model()->whereDate('datetested', $date)->orderBy('result', 'desc')->get();
		$last_update_data = $this->get_model()->whereRaw("DATE(datetested) < '{$date}'")->get();
		
		// Prepare the data to fill the excel
		$data = $this->prepareData($today_data, $last_update_data, $date);

		// Generate the excel
		$this->generateExcel($data, 'DAILY COVID-19 LABORATORY RESULTS ' . $date);
		return back();
	}

	private function get_model($lab_id = null)
	{
		$user = auth()->user();
		return CovidSampleView::where('repeatt', 0)
						->whereNotNull('result')
						->when($user, function ($query) use ($user, $lab_id) {
							if ($user->user_type_id == 12 && !$lab_id){
								return $query->where('lab_id', $user->lab_id);
							}
							if($lab_id) return $query->where('lab_id', $lab_id);
						})
						->when((env('APP_LAB') == 5), function($query){
							return $query->orderBy('worksheet_id', 'asc')
									->orderBy('run', 'desc')
									->orderBy('covid_sample_view.id', 'asc');
						});
	}

	private function generateExcel($data, $title)
	{
		Excel::create($title, function($excel) use ($data, $title) {
            $excel->setTitle($title);
            $excel->setCreator(auth()->user()->full_name)->setCompany('COVID-19 System');
            $excel->setDescription($title);

            $excel->sheet('Sheet1', function($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A4:H4');
                $sheet->mergeCells('A5:H5');
            });

        })->download('csv');
	}

	private function filterDate($model, $request)
	{
		if ($request->input('types') == 'daily_results_submission') 
		{
			$model = $model->whereDate('datetested', Carbon::now()->toDateString());
		} else {
			if ($request->input('period') == 'annually')
				$model = $model->whereYear('datetested', $request->input('year'));

			if ($request->input('period') == 'monthly')
				$model = $model->whereYear('datetested', $request->input('year'))
							->whereMonth('datetested', $request->input('month'));

			if ($request->input('period') == 'quarterly')
				$model = $model->whereYear('datetested', $request->input('year'))
							->whereRaw("MONTH(datetested) IN ({$this->quarters[$request->input('quarter')]})");

			if ($request->input('period') == 'range')
				$model = $model->whereRaw("DATE(datetested) BETWEEN '{$request->input('fromDate')}' AND '{$request->input('toDate')}'");
		}
	

		return $model;
	}

	private function prepareData($today_data, $last_update_data, $date)
	{
		$data = [[Lab::find(auth()->user()->lab_id)->labdesc . ' DAILY COVID-19 LABORATORY RESULTS SUBMISSION']];
		$data[] = [
			'Date', 'Testing Laboratory', 'Cumulative number of samples tested as at last update', 'Number of samples tested since last update', 'Cumulative number of samples tested to date ', 'Cumulative positive tests as at last update ', 'Number of new Positive tests', 'Cumulative Positive samples since onset of outbreak'
		];
		$data[] = $this->get_summary_data($today_data, $last_update_data, $date);

		if(env('APP_LAB') == 1 && auth()->user()->lab_id == env('APP_LAB')){
			$labs = CovidSample::selectRaw('DISTINCT lab_id AS lab_id')->where('lab_id', '!=', env('APP_LAB'))->where('site_entry', '!=', 2)->get();

			foreach ($labs as $key => $value) {
				// $today_data_other = $this->get_model($value->lab_id)->whereDate('datetested', $date)->orderBy('result', 'desc')->get();
				// $last_update_data_other = $this->get_model($value->lab_id)->whereDate("datetested", '<', $date)->get();
				// $data[] = $this->get_summary_data($today_data_other, $last_update_data_other, $date, $value->lab_id);


				$today_data_other = $this->get_model(15)->whereDate('datetested', $date)->orderBy('result', 'desc')->get();
				$last_update_data_other = $this->get_model(15)->whereRaw("DATE(datetested) < '{$date}'")->get();
				$data[] = $this->get_summary_data($today_data_other, $last_update_data_other, $date, 15);

				// $data[] = [$key, $value->lab_id, $value->toJson(), Lab::find($value->lab_id)->toJson()];
				// $data[] = $value->toArray();
				break;
			}
		}

		for ($i=0; $i < 2; $i++) { 
			$data[] = [""];
		}

		foreach ($this->get_detailed_data($today_data) as $key => $value) {
			$data[] = $value;
		}
		
		return $data;
	}

	private function get_summary_data($today_data, $last_update_data, $date, $lab_id=null)
	{
		if(!$lab_id) $lab_id = auth()->user()->lab_id;
		return [
			$date,
			Lab::find(15)->labdesc,
			$last_update_data->count(),
			$today_data->count(),
			($last_update_data->count() + $today_data->count()),
			$last_update_data->whereIn('result', [2,8])->count(),
			$today_data->whereIn('result', [2,8])->count(),
			($last_update_data->whereIn('result', [2,8])->count() + $today_data->whereIn('result', [2,8])->count())
		];
	}

	private function get_detailed_data($alldata)
	{
		$data = [['Testing Lab', 'S/N', 'Lab ID', 'Name', 'Age', 'Sex', 'ID/ Passport Number', 'Justification', 'Health Status',
				'Telephone Number', 'County of Residence', 'Sub-County', 'Travel History (Y/N)',
				'Where from', 'history of contact with confirmed case', 'Facility Name (Quarantine /health facility)', 'Name of Confirmed Case', 'Worksheet Number', 'Date Collected', 'Date Tested', 'Result', 'Test Type'
				]];
		$count = 1;
		$a = ['covid_justifications', 'health_statuses'];
		$lookups = [];
		foreach ($a as $value) {
			$lookups[$value] = DB::table($value)->get();
		}
		foreach ($alldata as $key => $row) {
			$data[] = $this->get_excel_samples($row, $count, $lookups);
			$count++;
		}
		return $data;
	}

	private function get_excel_samples($sample, $count, $lookups)
	{
		$travelled = 'N';
		$history = '';
		if (!$sample->patient->travel->isEmpty()){
			$travelled = 'Y';
			foreach ($sample->patient->travel as $key => $travel) {
				$history .= $travel->town->name . ', ' . $travel->town->country . '\n';
			}
		}
		return [
			// Lab::find(env('APP_LAB'))->labdesc,
			Lab::find($sample->lab_id)->labdesc,
			$count,
			$sample->id,
			$sample->patient_name,
			$sample->age,
			$sample->gender,
			$sample->identifier,
			$sample->get_prop_name($lookups['covid_justifications'], 'justification'),
			$sample->get_prop_name($lookups['health_statuses'], 'health_status'),
			$sample->phone_no ?? '',
			$sample->countyname ?? $sample->county,
			$sample->subcountyname ?? $sample->sub_county ?? $sample->subcounty ?? '',

			$travelled,
			$history,
			"",
			$sample->quarantine_site ?? $sample->facilityname ?? '',
			"",
			$sample->worksheet_id ?? '',
			$sample->datecollected ?? '',
			$sample->datetested ?? '',
			$sample->result_name,
			$sample->sampletype ?? ''
		];
	}
}
?>