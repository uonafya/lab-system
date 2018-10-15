<?php

namespace App;

use App\BaseModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Mail\CustomMail;
use App\Mail\CustomEmailFiles;

class Email extends BaseModel
{
    use SoftDeletes;

    /**
     * Get the user's full name
     *
     * @return string
     */
    public function getContentAttribute()
    {
        return $this->get_raw();
    }

    public function my_date_format_two($value)
    {
        if($this->$value) return date('Y-m-d', strtotime($this->$value));

        return '';
    }

    public function getSendingHourAttribute()
    {
        if($this->time_to_be_sent) return date('H', strtotime($this->time_to_be_sent));
        return null;
    }

    public function getSendingDayAttribute()
    {
        if($this->time_to_be_sent) return date('Y-m-d', strtotime($this->time_to_be_sent));
        return null;
    }

    public function demo_email($recepient)
    {
        $this->save_blade();
        $comm = new CustomMail($this, null);
        Mail::to([$recepient])->send($comm);
        $this->delete_blade();
    }


    public function dispatch()
    {
        $this->save_blade();
        ini_set("memory_limit", "-1");
        $facilities = \App\Facility::where('flag', 1)->get();

        $cc_array = [];
        $bcc_array = [];

        if($email->cc_list){
            $a = explode(',', $email->cc_list);

            foreach ($a as $key => $value) {
                if(str_contains($value, '@'))$cc_array[] = $value;
            }
        }

        if($email->bcc_list){
            $a = explode(',', $email->bcc_list);

            foreach ($a as $key => $value) {
                if(str_contains($value, '@'))$bcc_array[] = $value;
            }
        }
        $bcc_array = array_merge($bcc_array, ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@gmail.com']);

        foreach ($facilities as $key => $facility) {
        	$mail_array = $facility->email_array;
        	// $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
        	$comm = new CustomMail($this, $facility);
        	try {
	        	Mail::to($mail_array)->cc_array($cc_array)->bcc($bcc_array)->send($comm);
	        } catch (Exception $e) {
        	
	        }
        	// break;
        }
        $this->send_files();
        $this->delete_blade();
    }

    public function send_files()
    {
        $comm = new CustomEmailFiles($this);
        $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
        Mail::to($mail_array)->send($comm);
    }

    public function save_raw($email_string)
    {
    	if(!is_dir(storage_path('app/emails'))) mkdir(storage_path('app/emails'), 0777, true);

    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';

    	file_put_contents($filename, $email_string);
    }

    public function get_raw()
    {
    	if(!is_dir(storage_path('app/emails'))) mkdir(storage_path('app/emails'), 0777, true);

    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';
    	if(!file_exists($filename)) return null;
    	return file_get_contents($filename);
    }

    public function save_blade()
    {
    	$filename = storage_path('app/emails') . '/' . $this->id . '.txt';
    	$blade = base_path('resources/views/emails') . '/' . $this->id . '.php';

    	$str = file_get_contents($filename);
    	if($this->lab_signature) $str .= " @include('emails.lab_signature') ";
    	file_put_contents($blade, $str);
    }

    public function delete_blade()
    {
    	$blade = base_path('resources/views/emails') . '/' . $this->id . '.php';
    	unlink($blade);
    }

    public function delete_raw()
    {
        $filename = storage_path('app/emails') . '/' . $this->id . '.txt';
        if(file_exists($filename)) unlink($filename);
    }
}
