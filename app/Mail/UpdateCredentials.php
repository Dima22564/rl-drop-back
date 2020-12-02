<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpdateCredentials extends Mailable
{
    use Queueable, SerializesModels;

    private array $details;

  /**
   * Create a new message instance.
   *
   * @param array $details
   */
  public function __construct(array $details)
  {
    $this->details = $details;
  }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this->view('updateCredentials')
        ->with([
          'details' => $this->details
        ]);
    }
}
