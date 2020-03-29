<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidWorksheet extends BaseModel
{

	protected $dates = ['datecut', 'datereviewed', 'datereviewed2', 'dateuploaded', 'datecancelled', 'daterun'];

    public function sample()
    {
    	return $this->hasMany('App\CovidSample', 'sample_id');
    }

    public function runner()
    {
    	return $this->belongsTo('App\User', 'runby');
    }

    public function sorter()
    {
        return $this->belongsTo('App\User', 'sortedby');
    }

    public function bulker()
    {
        return $this->belongsTo('App\User', 'bulkedby');
    }

    public function quoter()
    {
        return $this->belongsTo('App\User', 'alliquotedby');
    }

    public function creator()
    {
    	return $this->belongsTo('App\User', 'createdby');
    }

    public function uploader()
    {
        return $this->belongsTo('App\User', 'uploadedby');
    }

    public function canceller()
    {
        return $this->belongsTo('App\User', 'cancelledby');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\User', 'reviewedby');
    }

    public function reviewer2()
    {
        return $this->belongsTo('App\User', 'reviewedby2');
    }

	public function other_samples($id = null){
		if(!$this->combined) return null;
		if($this->combined == 1){
			$class = Sample::class;
		}else{
			$class = Viralample::class;			
		}
		$dateuploaded = $this->dateuploaded;
		$samples = $class::where(['worksheet_id' => $worksheet_id])
			->when($this->status_id == 1, function($query){
				return $query->whereNull('datetested');
			})
			->when(in_array($this->status_id, [2,3]), function($query) use($dateuploaded){
				return $query->where('datemodified', $dateuploaded);
			})
			->when($id, function($query) use($id){
				return $query->where('id', $id);
			})
			->get();
		if($id) return $samples->first();
		return $samples;
	}

	public function update_other_samples($update_data){
		if(!$this->combined) return null;
		if($this->combined == 1){
			$class = Sample::class;
		}else{
			$class = Viralample::class;			
		}
		$dateuploaded = $this->dateuploaded;
		$class::where(['worksheet_id' => $worksheet_id])
			->when($this->status_id == 1, function($query){
				return $query->whereNull('datetested');
			})
			->when(in_array($this->status_id, [2,3]), function($query) use($dateuploaded){
				return $query->where('datemodified', $dateuploaded);
			})
			->update($update_data);
	}
}
