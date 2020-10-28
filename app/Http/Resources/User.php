<?php

namespace App\Http\Resources;

use App\Role;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PasswordSecurity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class User extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param \Illuminate\Http\Request $request
   * @return array
   */
  public function toArray($request)
  {
//    return parent::toArray($request);
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
      'phoneNumber' => $this->phone_number,
      'passwordSecurity' => new PasswordSecurity($this->passwordSecurity),
      'notifications' => $this->whenLoaded('customNotifications'),
      'balance' => $this->balance,
      'steamLink' => $this->steam_link,
      'xboxLink' => $this->xbox_link,
      'ps4Link' => $this->ps4_link,
      'photo' => $this->photo,
      'roles' => $this->when(true, $this->whenLoaded('roles'))
    ];
  }
}
