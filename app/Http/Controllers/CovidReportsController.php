<?php

namespace App\Http\Controllers;

use App\CovidSampleView;
use App\Lab;
use Carbon\Carbon;
use Excel;
use DB;
use Illuminate\Http\Request;

class CovidReportsController extends Controller
{
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

	private function get_model()
	{
		return CovidSampleView::where('repeatt', 0)->whereNotNull('result');
	}

	private function generateExcel($data, $title)
	{
		Excel::create($title, function($excel) use ($data, $title) {
            $excel->setTitle($title);
            $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('COVID-19 System');
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
		$data = [[Lab::find(env('APP_LAB'))->labdesc . ' DAILY COVID-19 LABORATORY RESULTS SUBMISSION']];
		$data[] = [
			'Date', 'Testing Laboratory', 'Cumulative number of samples tested as at last update', 'Number of samples tested since last update', 'Cumulative number of samples tested to date ', 'Cumulative positive tests as at last update ', 'Number of new Positive tests', 'Cumulative Positive samples since onset of outbreak'
		];
		$data[] = $this->get_summary_data($today_data, $last_update_data, $date);
		for ($i=0; $i < 2; $i++) { 
			$data[] = [""];
		}

		foreach ($this->get_detailed_data($today_data) as $key => $value) {
			$data[] = $value;
		}
		
		return $data;
	}

	private function get_summary_data($today_data, $last_update_data, $date)
	{
		return [
			$date,
			Lab::find(env('APP_LAB'))->labdesc,
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
		$data = [['Testing Lab', 'S/N', 'Name', 'Age', 'Sex', 'ID/ Passport Number', 'Justification', 'Health Status',
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
				$history .= $travel->city . ', ' . $travel->country . '\n';
			}
		}
		return [
			Lab::find(env('APP_LAB'))->labdesc,
			$count,
			$sample->patient_name,
			$sample->age,
			$sample->gender,
			$sample->identifier,
			$sample->get_prop_name($lookups['covid_justifications'], 'justification'),
			$sample->get_prop_name($lookups['health_statuses'], 'health_status'),
			$sample->phone_no ?? '',
			$sample->countyname ?? '',
			$sample->subcountyname ?? $sample->subcounty ?? '',

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