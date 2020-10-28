<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
  protected $table = 'withdraws';
  protected $fillable = ['messenger_id', 'status'];

  public const PENDING = 'pending';
  public const SUCCESS = 'success';
  public const FORBID = 'forbidden';

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
