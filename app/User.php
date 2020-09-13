<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements JWTSubject
{
  use HasApiTokens, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name', 'email', 'phone_number'
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
      ->withPivot(['date', 'item_id']);
  }

  public function items() {
    return $this->belongsToMany(Item::class, 'user_item')
      ->withPivot(['is_craft', 'sold', 'platform']);
  }

  public function changeBalance($price)
  {
    $user = User::find(Auth::user()->id);
    $user->balance += $price;
    $user->save();
  }

  public function isAdmin()
  {
    if ($this->is_admin == 1) {
      return true;
    }
    return false;
  }
}
