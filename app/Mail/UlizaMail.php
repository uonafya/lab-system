<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UlizaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $uliza_clinical_form;
    public $view_name;
    public $subject_title;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($uliza_clinical_form=null, $view_name=null, $subject_title='NASCOP')
    {
        $this->uliza_clinical_form = $uliza_clinical_form;
        $this->view_name = $view_name;
        $this->subject_title = $subject_title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject_title)->view('uliza.mail.' . $this->view_name);
    }
}
