<?php

namespace App\Mail;

use App\Batch;
use App\Lookup;

use Mpdf\Mpdf;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EidDispatch extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $batch;
    public $site_url;

    public $individual_path;
    public $summary_path;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Batch $batch)
    {

        $batch->load(['sample.patient.mother', 'facility', 'lab', 'receiver', 'creator']);
        $samples = $batch->sample;

        $this->batch = $batch;
        $sessionVar = md5('nasc0peId1234561987');
        $lab = auth()->user()->lab_id;
        $this->site_url ='http://www.nascop.org/eid/users/facilityresults.php?key='.$sessionVar.'&BatchNo='.$batch->id.'&LabID='.$lab.'&fauto='.$batch->facility->id;

        $this->individual_path = storage_path('app/batches/eid/individual-' . $batch->id . '.pdf');
        $this->summary_path = storage_path('app/batches/eid/summary-' . $batch->id . '.pdf');

        if(file_exists($this->individual_path)) unlink($this->individual_path);
        if(file_exists($this->summary_path)) unlink($this->summary_path);

        $mpdf = new Mpdf;
        $data = Lookup::get_lookups();
        $data = array_merge($data, ['batch' => $batch, 'samples' => $samples]);
        $view_data = view('exports.samples', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($this->individual_path, \Mpdf\Output\Destination::FILE);


        $mpdf = new Mpdf(['format' => 'A4-L']);
        $data = Lookup::get_lookups();
        $data = array_merge($data, ['batches' => [$batch]]);
        $view_data = view('exports.mpdf_samples_summary', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($this->summary_path, \Mpdf\Output\Destination::FILE);
        // DOMPDF::loadView('exports.samples_summary', $data)->setPaper('a4', 'landscape')->save($this->summary_path);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->attach($this->individual_path);
        $this->attach($this->summary_path);

        return $this->view('emails.eid_dispatch');
    }
}
