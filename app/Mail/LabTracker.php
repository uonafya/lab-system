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
    protected $path;
    protected $title;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
         $this->path = storage_path('app/lablogs/monthlabtracker' . $data->year . ' - '. $data->month .'.pdf');\

        if(!is_dir(storage_path('app/lablogs'))) mkdir(storage_path('app/lablogs'), 0777, true);

        if(file_exists($this->path)) unlink($this->path);

        $this->lab = \App\Lab::find(env('APP_LAB'));
        $lab = $this->lab;
        $view_data = view('exports.mpdf_labtracker', compact('data', 'lab'))->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($this->path, \Mpdf\Output\Destination::FILE);

        $this->title = $this->lab->labname . ' monthly lab tracker for '. date("F", mktime(null, null, null, $data->month)) . ' ' .$data->year;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->attach($this->path, ['as' => $this->title]);

        return $this->subject($this->title)->view('emails.labtracker');
    }
}
