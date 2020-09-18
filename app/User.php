<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;
use File;

class User extends Authenticatable implements JWTSubject
{
  use HasApiTokens, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name', 'email', 'phone_number', 'steam_link', 'xbox_link', 'ps4_link'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password', 'remember_token',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public function customNotifications()
  {
    return $this->hasMany(Notification::class);
  }

  public function getJWTIdentifier()
  {
    return $this->getKey();
  }

  public function getJWTCustomClaims()
  {
    return [];
  }

  public function cryptPassword($password)
  {
    $hashedPassword = Hash::make($password);
    $this->password = $hashedPassword;
    $this->save();
  }

  public function createRoom($roomId)
  {
    $this->room_id = $roomId;
    $this->save();
  }

  public function passwordSecurity()
  {
    return $this->hasOne('App\PasswordSecurity');
  }

  public function chests()
  {
    return $this->belongsToMany(Chest::class, 'user_chest')
      ->withPivot(['date']);
  }

  public function items()
  {
    return $this->belongsToMany(Item::class, 'user_item')
      ->withPivot(['is_craft', 'sold', 'platform', 'id']);
  }

  public function changeBalance($price)
  {
    $user = User::find(Auth::user()->id);
    $user->balance += $price;
    $user->save();
  }

  public function isAdmin()
  {
    if ($this->is_admin === 1) {
      return true;
    }
    return false;
  }

  public function savePhoto($image)
  {
    $filename = (string)$this->id . '.' . $image->extension();
    $path1 = 'app/public/uploads/users/' . (string)Auth::user()->id . '.png';
    $path2 = 'app/public/uploads/users/' . (string)Auth::user()->id . '.jpg';

    if (file_exists(storage_path($path1))) {
      unlink(storage_path('app/public/uploads/users/' . (string)$this->id . '.png'));
    } elseif (file_exists(storage_path($path2))) {
      unlink(storage_path('app/public/uploads/users/' . (string)$this->id . '.jpg'));
    }
    $r = $image->storeAs('public/uploads/users', $filename);
    $storagePath = env('APP_URL', 'http://127.0.0.1:8000') . Storage::url('uploads/users/' . $filename);
    $this->photo = $storagePath;
    $this->save();
  }
}
