<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kits extends BaseModel
{
	protected $year;
    protected $previousYear;
    protected $month;
    protected $previousMonth;

    public function __construct(){
        $this->year = date('Y');
        $this->month = date('m');

        $this->previousYear = $this->year;
        $this->previousMonth = $this->month - 1;
        if ($this->month == 1) {
            $this->previousMonth = 12;
            $this->previousYear = $this->year-1;
        }
    }

    public function machine() {
    	return $this->belongsTo('App\Machine');
    }

    public function consumption_lines(){
    	return $this->hasMany('App\ConsumptionDetail', 'kit_id');
    }

    public function lastMonth(){
    	return $this->consumption->where('year', '=', $this->previousYear)->where('month', '=', $this->previousMonth);
    }
   
    public function commodities()
    {
        return $this->morphMany('App\AllocationDetailsBreakdown', 'breakdown');
    }
   
    public function deliveredkits()
    {
        return $this->morphMany('App\DeliveryDetail', 'kit');
    }

    public function consumption_headers()
    {
        return $this->consumption_lines->load('header')->pluck('header')->flatten();
    }

    public function lastMonthConsumption($type = null)
    {
        $lastmonthYear = date('Y', strtotime("-1 Month", strtotime(date('Y-m-d'))));// Get the year in which last month belonged to (comes in handy especialy in January)
        $lastmonth = date('m', strtotime("-1 Month", strtotime(date('Y-m-d'))));
        $id = $this->id;
        $lastmonth_header = $this->consumption_headers()
                                ->where('year', $lastmonthYear)
                                ->where('month', $lastmonth)
                                ->where('type', $type)
                                ->transform(function($header, $key) use ($id){
                                    $line = $header->details->where('kit_id', $id)->first();
                                    $line->year = $header->year;
                                    $line->month = $header->month;
                                    $line->type = $header->type;
                                    return $line;
                                });
        return $lastmonth_header;
    }
}