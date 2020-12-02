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
    'ps4_price',
    'category',
    'is_limited',
    'max_open',
    'current_open'
  ];

  public function items()
  {
    return $this->belongsToMany(Item::class, 'chests_items')
      ->withPivot('weight', 'item_id', 'created_at');
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

  public function saveItems($items, $update = false)
  {
    $itemsIds = array_column($items, 'id');
    $weights = array_column($items, 'weight');
    if ($update) {
      $this->items()->detach($itemsIds);
    }
    foreach ($itemsIds as $key => $id) {
      $this->items()->attach($id, ['weight' => $weights[$key]]);
    }
  }

  public function saveImage($image)
  {
    $filename = $this->id . '.' . $image->extension();
    $path1 = 'app/public/uploads/chests/' . $this->id . '.png';
    $path2 = 'app/public/uploads/chests/' . $this->id . '.jpg';

    if (file_exists(storage_path($path1))) {
      unlink(storage_path('app/public/uploads/chests/' . (string)$this->id . '.png'));
    } elseif (file_exists(storage_path($path2))) {
      unlink(storage_path('app/public/uploads/chests/' . (string)$this->id . '.jpg'));
    }
    $r = $image->storeAs('public/uploads/chests', $filename);
    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/chests/' . $filename);
    $this->image = $storagePath;
    $this->save();
  }

  public function saveBackgroundImage($image)
  {
    $filename = $this->id . '-bg' . '.' . $image->extension();
    $path1 = 'app/public/uploads/chests/' . $this->id . '-bg' . '.png';
    $path2 = 'app/public/uploads/chests/' . $this->id . '-bg' . '.jpg';

    if (file_exists(storage_path($path1))) {
      unlink(storage_path('app/public/uploads/chests/' . (string)$this->id . '-bg' . '.png'));
    } elseif (file_exists(storage_path($path2))) {
      unlink(storage_path('app/public/uploads/chests/' . (string)$this->id . '-bg' . '.jpg'));
    }
    $r = $image->storeAs('public/uploads/chests', $filename);
    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/chests/' . $filename);
    $this->background_image = $storagePath;
    $this->save();
  }

  public function addOpen()
  {
    $this->current_open += 1;
    $this->save();
  }


}
