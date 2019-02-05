<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $attachments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attachments = null)
    {
        $this->attachments = $attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->attachments && is_array($this->attachments)){
            foreach ($this->attachments as $key => $value) {
                $this->attach($value, ['as' => $key]);
            }
        }
        return $this->view('emails.test');
    }
}
