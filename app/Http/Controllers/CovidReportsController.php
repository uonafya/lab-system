<?php

namespace App\Http\Controllers;

use App\CovidSampleView;
use App\Lab;
use Carbon\Carbon;
use Excel;
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
		$yesterday = Carbon::now()->toDateString();
		$today_data = $this->get_model()->whereDate('datetested', Carbon::now()->toDateString())->get();
		$yesterday_data = $this->get_model()->whereRaw("DATE(datetested) < '{$yesterday}'")->get();
		$alldata = $this->get_model()->whereNotIn('result', [3])->orderBy('result', 'desc')->get();
		// dd($alldata);
		$data = $this->prepareData($today_data, $yesterday_data, $alldata);
		// dd($data);
		$this->generateExcel($data, 'DAILY COVID-19 LABORATORY RESULTS ' . date('YmdHis'));
		// return back();
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

	private function prepareData($today_data, $yesterday_data, $alldata)
	{
		$data = [['DAILY COVID-19 LABORATORY RESULTS SUBMISSION']];
		$data[] = [
			'Date', 'Testing Laboratory', 'Cumulative number of samples tested as at last update', 'Number of samples tested since last update', 'Cumulative number of samples tested to date ', 'Cumulative positive tests as at last update ', 'Number of new Positive tests', 'Cumulative Positive samples since onset of outbreak'
		];
		$data[] = $this->get_summary_data($today_data, $yesterday_data);
		for ($i=0; $i < 2; $i++) { 
			$data[] = [""];
		}

		foreach ($this->get_detailed_data($today_data) as $key => $value) {
			$data[] = $value;
		}
		
		return $data;
	}

	private function get_summary_data($today_data, $yesterday_data)
	{
		return [
			Carbon::now()->format('d/m/Y'),
			Lab::find(env('APP_LAB'))->labdesc,
			$yesterday_data->count(),
			$today_data->count(),
			($yesterday_data->count() + $today_data->count()),
			$yesterday_data->where('result', 2)->count(),
			$today_data->whereIn('result', [2,8])->count(),
			($yesterday_data->whereIn('result', [2,8])->count() + $today_data->whereIn('result', [2,8])->count())
		];
	}

	private function get_detailed_data($alldata)
	{
		$data = [['Testing Lab', 'S/N', 'Name', 'Age', 'Sex', 'ID/ Passport Number',
				'Telephone Number', 'County of Residence', 'Sub-County', 'Travel History (Y/N)',
				'Where from', 'Facility Name (Quarantine /health facility)', 'Date Tested', 'Result',
				'Test Type'
				]];
		$count = 1;
		foreach ($alldata as $key => $row) {
			$data[] = $this->get_excel_samples($row, $count);
			$count++;
		}
		return $data;
		// $detail_header = ['Testing Lab', 'S/N'];
		// $positives = $this->get_excel_samples($today_data->where('result', 2));
		// $positives = $positives->push($this->get_excel_samples($yesterday_data->where('result', 2)));
		// $negatives = $this->get_excel_samples($today_data->where('result', 1));
		// $negatives = $negatives->push($this->get_excel_samples($yesterday_data->where('result', 1)));
		
		// $data[] = ['POSITIVES'];
		// $data[] = $detail_header;
		// $data[] = $positives;

		// $data[] = ['NEGATIVES'];
		// $data[] = $detail_header;
		// $data[] = $negatives;
		// return $data;
	}

	private function get_excel_samples($sample, $count)
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
			$sample->patient->gender,
			$sample->identifier,
			$sample->phone ?? '',
			$sample->patient->county->name ?? '',
			$sample->subcounty ?? '',
			$travelled,
			$history,
			$sample->patient->quarantine_site->name ?? '',
			$sample->datetested ?? '',
			$sample->result_name,
			$sample->sampletype ?? ''
		];
	}
}
?>