<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IndexBottomBanner extends Model
{
  protected $table = 'index_bottom_banner';
  protected $fillable = [
    'name',
    'title',
    'text_ru',
    'text_en',
    'case_id',
    'image',
    'mobile_image'
  ];
  public $timestamps = false;

  public function saveImage($image)
  {
    $filename = $image['name'] . '.' . $image['image']->extension();
    $path1 = 'app/public/uploads/banners/' . $image['name'] . '.png';
    $path2 = 'app/public/uploads/banners/' . $image['name'] . '.jpg';

    if (file_exists(storage_path($path1))) {
      unlink(storage_path($path1));
    } elseif (file_exists(storage_path($path2))) {
      unlink(storage_path($path2));
    }
    $r = $image['image']->storeAs('public/uploads/banners', $filename);
    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/banners/' . $filename);
    $this->image = $storagePath;
    $this->save();
  }

  public function saveMobileImage($image)
  {
    $filename = $image['name'] . '-mobile.' . $image['image']->extension();
    $path1 = 'app/public/uploads/banners/' . $image['name'] . '-mobile.png';
    $path2 = 'app/public/uploads/banners/' . $image['name'] . '-mobile.jpg';

    if (file_exists(storage_path($path1))) {
      unlink(storage_path($path1));
    } elseif (file_exists(storage_path($path2))) {
      unlink(storage_path($path2));
    }
    $r = $image['image']->storeAs('public/uploads/banners', $filename);
    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/banners/' . $filename);
    $this->mobile_image = $storagePath;
    $this->save();
  }
}
