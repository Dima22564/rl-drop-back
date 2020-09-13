<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PasswordSecurity extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param \Illuminate\Http\Request $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'id' => $this->id,
//      'user_id' => $this->user_id,
      'google2fa_enable' => $this->google2fa_enable,
      'google2fa_secret' => $this->google2fa_secret
    ];
  }
}
