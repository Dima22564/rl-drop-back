<?php

namespace App\Jobs;

use App\Mail\PasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangeCredentials;

class SendConfirmLink implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private array $details;


  /**
   * Create a new job instance.
   *
   * @param array $details
   */
  public function __construct(array $details)
  {
    $this->details = $details;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $email = new ChangeCredentials($this->details);
    Mail::to($this->details['email'])->send($email);
  }
}
