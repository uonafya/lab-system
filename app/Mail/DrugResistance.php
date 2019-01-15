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
        $form_url = URL::temporarySignedRoute('dr_sample.facility_edit', now()->addDays(3), ['user' => $user->id, 'sample' => $this->sample->id]);
        $form_url2 = URL::temporarySignedRoute('dr_sample.facility_edit', now()->addDays(3), ['user' => $user->id, 'sample' => $this->sample->id]);

        // This is because the application receives requests on http but forces it to https

        \Illuminate\Support\Facades\URL::forceScheme('http');
        $url = URL::temporarySignedRoute('dr_sample.facility_edit', now()->addDays(3), ['user' => $user->id, 'sample' => $this->sample->id]);
        \Illuminate\Support\Facades\URL::forceScheme('https');

        $new_signature = str_after($url, 'signature=');
        $old_signature = str_after($form_url, 'signature=');
        $old_signature2 = str_after($form_url2, 'signature=');

        // dd(['old_signature' => $old_signature, 'old_signature2' => $old_signature2, 'new_signature' => $new_signature]);

        $this->form_url = str_replace($old_signature, $new_signature, $form_url);

        $data = Lookup::get_dr();

        if(!is_dir(storage_path('app/dr/'))) mkdir(storage_path('app/dr'), 0777, true);

        $path = storage_path('app/dr/sample-' . $this->sample->id . '.pdf');
        $data['sample'] = $this->sample;

        if(file_exists($path)) unlink($path);

        $mpdf = new Mpdf(['format' => 'A4']);
        $view_data = view('exports.mpdf_dr', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

        $this->attach($path);
        return $this->view('emails.dr');
    }
}
