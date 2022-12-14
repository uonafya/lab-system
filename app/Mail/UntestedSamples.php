<?php
//
//namespace App\Mail;
//
//use Illuminate\Bus\Queueable;
//use Illuminate\Mail\Mailable;
//use Illuminate\Queue\SerializesModels;
//use Illuminate\Contracts\Queue\ShouldQueue;
//
//use DB;
//
//use App\Lookup;
//use App\Lab;
//
//class UntestedSamples extends Mailable
//{
//    use Queueable, SerializesModels;
//
//    public $data;
//
//    /**
//     * Create a new message instance.
//     *
//     * @return void
//     */
//    public function __construct($data)
//    {
//
//        $this->data =$data;
//
//    }
//
//    /**
//     * Build the message.
//     *
//     * @return $this
//     */
//    public function build()
//    {
//        $this->attach($this->data);
//
//        return $this->subject("UNTESTED SAMPLES")->view('emails.untested');
//    }
//}


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UntestedSamples extends Mailable
{
    use Queueable, SerializesModels;

    public $my_attachments;
    public $sub;
    public $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($my_attachments = null, $sub = null, $text = null)
    {
        $this->my_attachments = $my_attachments;
        $this->sub = $sub;
        $this->text = $text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->my_attachments && is_array($this->my_attachments)) {
            foreach ($this->my_attachments as $key => $value) {
                // $this->attach($value, ['as' => $key]);
                $this->attach($value);
            }
        }
        if ($this->sub) $this->subject($this->sub);
        return $this->view('emails.untested');
    }
}
