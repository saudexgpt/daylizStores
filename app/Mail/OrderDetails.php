<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class OrderDetails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user; //make it public so that the view resource can access it
    public $order;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $order)
    {
        //
        $this->user = $user;
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = $this->user;
        $order = $this->order;
        return $this->view('emails.order_details', compact('user', 'order'));
    }
}
