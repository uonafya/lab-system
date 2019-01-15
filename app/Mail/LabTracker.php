<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LabTracker extends Mailable
{
    use Queueable, SerializesModels;

    protected $lab;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->lab = \App\Lab::find(env('APP_LAB'));
        $lab = $this->lab;
        $view_data = view('exports.mpdf_samples', compact('data', 'lab'))->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($this->individual_path, \Mpdf\Output\Destination::FILE);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.labtracker');
    }
}
