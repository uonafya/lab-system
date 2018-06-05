<?php

namespace App\Mail;

use App\Viralbatch;
use App\Lookup;

use DOMPDF;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VlDispatch extends Mailable implements ShouldQueue
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
    public function __construct(Viralbatch $batch)
    {
        $batch->load(['sample.patient', 'facility', 'lab', 'receiver', 'creator']);
        $samples = $batch->sample;
        $this->batch = $batch;
        $sessionVar = md5('nasc0peId1234561987');
        $lab = auth()->user()->lab_id;
        $this->site_url ='http://www.nascop.org/eid/users/facilityresults.php?key='.$sessionVar.'&BatchNo='.$batch->id.'&LabID='.$lab.'&fauto='.$batch->facility->id;

        // $this->individual_path = storage_path('app/batches/vl/individual-' . $batch->id . '.pdf');
        // $this->summary_path = storage_path('app/batches/vl/summary-' . $batch->id . '.pdf');

        $this->individual_path = public_path('batches/vl/individual-' . $batch->id . '.pdf');
        $this->summary_path = public_path('batches/vl/summary-' . $batch->id . '.pdf');

        if(file_exists($this->individual_path)) unlink($this->individual_path);
        if(file_exists($this->summary_path)) unlink($this->summary_path);

        $data = Lookup::get_viral_lookups();
        $data = array_merge($data, ['batch' => $batch, 'samples' => $samples]);
        DOMPDF::loadView('exports.viralsamples', $data)->setPaper('a4', 'landscape')->save($this->individual_path);

        $data = Lookup::get_viral_lookups();
        $data = array_merge($data, ['batches' => [$batch]]);
        DOMPDF::loadView('exports.viralsamples_summary', $data)->setPaper('a4', 'landscape')->save($this->summary_path);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.eid_dispatch')
        ->attach($this->individual_path, [
            'as' => 'individual-' . $this->batch->id . '.pdf',
            'mime' => 'application/pdf',
        ])
        ->attach($this->summary_path, [
            'as' => 'summary-' . $this->batch->id . '.pdf',
            'mime' => 'application/pdf',
        ]);
    }
}
