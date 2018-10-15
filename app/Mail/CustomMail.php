<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use \App\Lab;

class CustomMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $facility;
    public $lab;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $facility=null)
    {
        $this->email = $email;
        $this->facility = $facility;
        $this->lab = Lab::find(env('APP_LAB'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view_name = 'emails.' . $this->email->id;
        $from = $this->email->from_name ?? env('MAIL_FROM_NAME');
        $from = env('MAIL_FROM_NAME');
        return $this->subject($this->email->subject)->from(env('MAIL_FROM_ADDRESS'), $from)->view($view_name);
        return $this->subject($this->email->subject)->view($view_name);
    }
}
