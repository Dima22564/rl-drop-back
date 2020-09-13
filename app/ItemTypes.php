<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemTypes extends Model
{
  protected $table = 'items_types';

  protected $fillable = ['type'];

  public function items() {
    return $this->hasMany(Item::class, 'type_id');
  }
}
