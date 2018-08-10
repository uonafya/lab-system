<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Support\Facades\URL;
use Mpdf\Mpdf;

use App\DrSample;
use App\Lookup;
use App\User;

class DrugResistance extends Mailable
{
    use Queueable, SerializesModels;

    public $sample;
    public $form_url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DrSample $sample)
    {
        $this->sample = $sample;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->sample->load(['patient.facility']);
        $user = User::where(['user_type_id' => 5, 'facility_id' => $this->sample->patient->facility->id])->first();
        $this->form_url = URL::temporarySignedRoute('dr_sample.edit', now()->addDays(3), ['user' => $user->id]);
        $data = Lookup::get_dr();

        $path = storage_path('app/dr/sample' . $this->sample->id . '.pdf');
        $data['sample'] = $this->sample;

        if(file_exists($path)) unlink($path);

        $mpdf = new Mpdf;
        $view_data = view('exports.mpdf_dr', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

        $this->attach($path);
        return $this->view('emails.dr');
    }
}
