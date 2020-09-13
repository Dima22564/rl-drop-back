<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chest extends Model
{
  use SoftDeletes;

  protected $table = 'chests';

  protected $fillable = [
    'old_price',
    'is_case_visible_for_user',
    'name',
    'xbox_price',
    'pc_price',
    'ps4_price'
  ];

  public function items()
  {
    return $this->belongsToMany(Item::class, 'chests_items')
      ->withPivot('weight');
  }

  public function users()
  {
    return $this->belongsToMany(User::class, 'user_chest')
      ->withPivot(['date', 'item_id']);
  }

  public function winItems()
  {
    return $this->belongsToMany(Item::class, 'chest_item_win');
  }

  public function saveImage($image)
  {
    $filename = $this->id . '.' . $image->extension();
    $r = $image->storeAs('public/uploads/chests', $filename);
    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/chests/' . $filename);
    $this->image = $storagePath;
    $this->save();
  }

  public function chooseItem()
  {
//    $this->items->each(function ($item, $key) {
//      $arr = [];
//      $probability = round($item->probability / 100 * 20);
//      for ($i = 0; $i < $probability; $i++) {
//        array_push($arr, 1);
//      }
//      for ($i = 0; $i < 20 - $probability; $i++) {
//        array_push($arr, 0);
//      }
//
//      shuffle($arr);
//
//      $randomElement = rand(0, 20);
//
//      return $arr[$randomElement];
//    });

  }

  public function saveItems($items)
  {
    $itemsIds = array_column($items, 'id');
    $weights = array_column($items, 'weight');
    foreach ($itemsIds as $key => $id) {
      $this->items()->attach($id, ['weight' => $weights[$key]]);
    }
  }


}
