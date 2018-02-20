<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Sample;
use DB;

class Misc extends Model
{

	public function requeue($worksheet_id)
	{
		$samples = Sample::where('worksheet_id', $worksheet_id)->get();

		// Default value for repeatt is 0

		foreach ($samples as $sample) {
			if($sample->parentid == 0){
				if($sample->result == 2 || $sample->result == 3){
					$sample->repeatt = 1;
					$sample->save();
				}
			}
			else{
				$original = $this->check_original($sample_id);

				if($sample->run == 2){
					if( ($sample->result == 3 && $original->result == 3) || 
						($sample->result == 2 && $original->result == 3) || 
						($sample->result != 2 && $original->result == 2) )
					{
						$sample->repeatt = 1;
						$sample->save();
					}
				}

				else if($sample->run == 3){
					$second = $this->check_run($sample_id, 2);

					if( ($sample->result == 3 && $second->result == 2 && $original->result == 3) ||
						($original->result == 2 && $second->result == 1 && $sample->result == 2) ||
						($original->result == 2 && $second->result == 3 && $sample->result == 3) )
					{
						$sample->repeatt = 1;
						$sample->save();
					}
				}
			}
		}
		return true;
	}

	public function save_repeat($sample_id)
	{
		$sample = new Sample;
		$sample->fill( Sample::find($sample_id)->toArray() );

		if($sample->run == 4){
			return false;
		}

		if($sample->parentid == 0){
			$sample->parentid = $sample->id;
		}
		$sample->run = $sample->run + 1;
		$sample->id = $sample->worksheet_id = $sample->inworksheet = $sample->result = $sample->interpretation = $sample->approvedby = $sample->approvedby2 = $sample->datemodified = $sample->dateapproved = $sample->dateapproved2 = $sample->created_at = $sample->updated_at = null;
		$sample->repeatt = $sample->inworksheet = $sample->synched = 0;
		$sample->created_at = date('Y-m-d');

		$sample->save();
		return $sample;
	}

	public function check_batch($batch)
	{		
		$total = Sample::where('batch_id', $batch)->where('parentid', 0)->get()->count();
		$tests = Sample::where('batch_id', $batch)
		->whereRaw("( receivedstatus=2 OR  (result > 0 AND repeatt = 0 AND approvedby IS NOT NULL) )")
		->get()
		->count;

		if($total == $tests){
			DB::table('batches')->where('id', $batch)->update(['batch_complete' => 2]);
		}
	}

	public function check_original($sample_id)
	{
		$lab = session()->auth()->id;

		$sample = Sample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.id' => $sample_id])
		->get()
		->first();

		return $sample;
	}

	public function check_previous($sample_id)
	{
		$lab = session()->auth()->id;

		$samples = Sample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.parentid' => $sample_id])
		->get();

		return $samples;
	}

	public function check_run($sample_id, $run=2)
	{
		$lab = session()->auth()->id;

		$sample = Sample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.parentid' => $sample_id, 'run' => $run])
		->get()
		->first();

		return $sample;
	}
}
