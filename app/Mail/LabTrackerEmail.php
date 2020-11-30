<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Mpdf\Mpdf;

class LabTrackerEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $file_path;
    public $data;
    public $lab;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $lab)
    {
        $this->data = $data;
        $this->file_path = storage_path('app/lablogs/monthlabtracker ' . $data->year .  $data->month .'.pdf');
        $this->lab = $lab;

        if(!is_dir(storage_path("app/lablogs/"))) mkdir(storage_path("app/lablogs/"), 0777, true);
        
        $mpdf = new Mpdf(['format' => 'A4-L']);
        $view_data = view('exports.mpdf_labtracker', ['data' => $data, 'lab' => $lab, 'download' => false])->render();
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
        $monthname = date("F", mktime(0, 0, 0, $data->month, 10));
        $str = "{$this->lab->desc} Labtracker Report For {$data->year} - ";
        $this->attach($this->file_path, ['as' => $str]);
        return $this->subject($str)->view('emails.labtracker');
    }
}
