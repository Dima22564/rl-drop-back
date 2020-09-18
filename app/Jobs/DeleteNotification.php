<?php

namespace App\Jobs;

use App\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteNotification implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  private $id;
  public function __construct($id)
  {
    $this->id = $id;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    Notification::destroy($this->id);
  }
}
