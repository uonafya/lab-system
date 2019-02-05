<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $my_attachments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($my_attachments = null)
    {
        $this->my_attachments = $my_attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->my_attachments && is_array($this->my_attachments)){
            foreach ($this->my_attachments as $key => $value) {
                // $this->attach($value, ['as' => $key]);
                $this->attach($value);
            }
        }
        return $this->view('emails.test');
    }
}
