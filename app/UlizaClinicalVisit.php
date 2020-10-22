<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mpdf\Mpdf;
use DB;

class UlizaClinicalVisit extends BaseModel
{


    public function clinical_form()
    {
        return $this->belongsTo('App\UlizaClinicalForm');
    }

    public function getEntryPathAttribute()
    {
    	return storage_path('app/batches/uliza/entry-' . $this->id . '.pdf');
    }

    public function entry_pdf($file_path=null, $download=false)
    {
    	if(!$file_path) $file_path = $this->entry_path;

    	if(file_exists($file_path)) unlink($file_path);

    	$file_name = explode('/', $file_path);
    	$file_name = array_pop($file_name);


        $mpdf = new Mpdf();
        $reasons = DB::table('uliza_reasons')->where('public', 1)->get();
        $regimens = DB::table('viralregimen')->get();
        $ulizaClinicalForm = $this;
        $view_data = view('uliza.exports.clinical_form', compact('reasons', 'regimens', 'ulizaClinicalForm'))->render();
        $mpdf->WriteHTML($view_data);

        if($download) return $mpdf->Output($this->individual_path, \Mpdf\Output\Destination::DOWNLOAD);
        else{
	        $mpdf->Output($this->individual_path, \Mpdf\Output\Destination::FILE);
        }
    }

}
