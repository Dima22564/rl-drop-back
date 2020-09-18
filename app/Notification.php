<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
  protected $table = 'notifications';

  protected $fillable = ['text_ru', 'text_en', 'type', 'user_id', 'date'];

  const SUCCESS = 'success';
  const WARNING = 'warning';
  const FINANCIAL = 'financial';
  const MESSAGE = 'message';

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
