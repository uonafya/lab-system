<?php

namespace App\Mail;

use App\Batch;
use DB;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EidDispatch extends Mailable
{
    use Queueable, SerializesModels;

    public $batch;
    public $facility;
    public $view_facility;
    public $site_url;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Batch $batch, $facility)
    {
        $batch->load(['sample']);
        $this->batch = $batch;
        $this->facility = $facility;
        $this->view_facility = DB::table('view_facilitys')->where('id', $batch->facility_id)->get()->first();
        $sessionVar = md5('nasc0peId1234561987');
        $lab = auth()->user()->id;
        $this->site_url ='http://www.nascop.org/eid/users/facilityresults.php?key='.$sessionVar.'&BatchNo='.$batch->id.'&LabID='.$lab.'&fauto='.$facility->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.eid_dispatch');
    }
}
