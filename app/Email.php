<?php

namespace App;

use App\BaseModel;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomMail;

class Email extends BaseModel
{

    public function dispatch()
    {
        ini_set("memory_limit", "-1");
        $facilities = \App\Facility::where('flag', 1)->get();

        foreach ($facilities as $key => $facility) {
        	$mail_array = $facility->email_array;
        	// $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
        	$comm = new CustomMail($this, $facility);
        	try {
	        	Mail::to($mail_array)->bcc(['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@gmail.com'])
	        	->send($comm);
	        } catch (Exception $e) {
        	
	        }
        	// break;
        }
    }

    public function save_raw($email_string)
    {
    	if(!is_dir(storage_path('app/emails'))) mkdir(storage_path('app/emails'), 0777, true);

    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';

    	file_put_contents($filename, $email_string);
    }

    public function save_blade()
    {
    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';
    	$blade = base_path('resources/views/emails') . '/' . $this->id . '.txt';

    	$str = file_get_contents($filename);
    	if($this->lab_signature) $str .= " @include('emails.lab_signature') ";
    	file_put_contents($blade, $str);
    }

    public function delete_blade()
    {
    	$blade = base_path('resources/views/emails') . '/' . $this->id . '.txt';
    	unlink($blade);
    }
}
