<?php

namespace App\Mail;

use App\Batch;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EidDispatch extends Mailable
{
    use Queueable, SerializesModels;

    public $batch;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('view.name');
    }
}
