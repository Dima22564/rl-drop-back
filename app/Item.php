<?php

namespace App;

use Defuse\Crypto\File;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
  use SoftDeletes;

  protected $table = 'items';

  protected $fillable = [
    'name',
    'xbox_price',
    'pc_price',
    'ps4_price',
    'appear_in_chest',
    'appear_in_craft',
    'color',
    'text',
    'type_id'
  ];

//  public const PERCENT_OF_ITEMS = 20;

  public function type()
  {
    return $this->belongsTo(ItemTypes::class);
  }

  public function chests()
  {
    return $this->belongsToMany(Chest::class, 'chests_items')
      ->withPivot('weight');
  }

  public function openChests()
  {
    return $this->belongsToMany(Chest::class, 'chest_item_win');
  }

  public function users()
  {
    return $this->belongsToMany(User::class, 'user_item')
      ->withPivot(['is_craft']);
  }

  public static function chooseRandomItem($items)
  {
    $count = count($items);
    $i = 0;
    $n = 0;
    $weights = array_column($items, 'weight');
    $num = mt_rand(1, array_sum($weights));
    while ($i < $count) {
      $n += $weights[$i];
      if ($n >= $num) {
        break;
      }
      $i++;
    }

//    $item = Item::find($items[$i]->item_id);
    return Item::with('type')->where('id', $items[$i]['item_id'])->first();
  }

  public function craftItem($progress)
  {
    $arr = [];
    for ($i = 0; $i < 100; $i++) {
      if ($i < $progress) {
        array_push($arr, 1);
      } else {
        array_push($arr, 0);
      }
    }
    shuffle($arr);
    $num = mt_rand(0, 100);
    return $arr[$num];
  }
//
//  public static function numberOfItems($percent, $platform)
//  {
//    return floor(parent::where('platform', $platform)->count() * $percent / 100);
//  }

//  public function saveImage($image)
//  {
//    $filename = $this->id . '.' . $image->extension();
//    $r = $image->storeAs('public/uploads/items', $filename);
//    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/items/' . $filename);
//    $this->image = $storagePath;
//    $this->save();
//  }

  public function saveImage($image)
  {
    $filename = $this->id . '.' . $image->extension();
    $path1 = 'app/public/uploads/items/' . $this->id . '.png';
    $path2 = 'app/public/uploads/items/' . $this->id . '.jpg';

    if (file_exists(storage_path($path1))) {
      unlink(storage_path('app/public/uploads/items/' . (string)$this->id . '.png'));
    } elseif (file_exists(storage_path($path2))) {
      unlink(storage_path('app/public/uploads/items/' . (string)$this->id . '.jpg'));
    }
    $r = $image->storeAs('public/uploads/items', $filename);
    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/items/' . $filename);
    $this->image = $storagePath;
    $this->save();
  }
}
