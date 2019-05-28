<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mpdf\Mpdf;

class CriticalResults extends Mailable
{
    use Queueable, SerializesModels;

    public $facility;
    public $type;
    public $samples;
    public $file_path;
    public $datedispatched;
    public $lab;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($facility, $type, $samples, $datedispatched)
    {
        $this->facility = $facility;
        $this->type = $type;
        $this->samples = $samples;
        $this->datedispatched = $datedispatched;
        $this->file_path = storage_path('app/critical/' . $type . '_' . $facility->id . '.pdf');
        $this->lab = \App\Lab::find(env('APP_LAB'));

        if(!is_dir(storage_path("app/critical/"))) mkdir(storage_path("app/critical/"), 0777, true);

        $mpdf = new Mpdf();
        $view_data = view('exports.mpdf_critical', ['samples' => $samples, 'facility' => $facility, 'type' => $type])->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($this->file_path, \Mpdf\Output\Destination::FILE);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $t = strtoupper($this->type);
        $str = "Critical {$t} Results For {$this->datedispatched}";
        $this->attach($this->file_path, ['as' => $str]);
        return $this->subject($str)->view('emails.critical');
    }
}
