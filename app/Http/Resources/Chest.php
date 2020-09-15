<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Item as ItemResource;
use Illuminate\Support\Facades\Auth;

class Chest extends JsonResource
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
      'oldPrice' => $this->old_price,
      'image' => $this->image,
      'visibility' => $this->when(Auth::user() ? Auth::user()->isAdmin() : false, $this->is_case_visible_for_user),
      'xboxPrice' => $this->xbox_price,
      'pcPrice' => $this->pc_price,
      'ps4Price' => $this->ps4_price,
      'items' => ItemResource::collection($this->whenLoaded('items')),
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
