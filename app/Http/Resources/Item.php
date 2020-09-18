<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Chest as ChestResource;
use App\Http\Resources\ItemType as ItemTypeResource;
use Illuminate\Support\Facades\Auth;

class Item extends JsonResource
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
      'xboxPrice' => $this->xbox_price,
      'pcPrice' => $this->pc_price,
      'ps4Price' => $this->ps4_price,
      'image' => $this->image,
      'sold' => $this->whenPivotLoaded('user_item', function () {
        return $this->pivot->sold;
      }),
      'platform' => $this->whenPivotLoaded('user_item', function () {
        return $this->pivot->platform;
      }),
      'pivot' => [
        'id' => $this->whenPivotLoaded('user_item', function () {
          return $this->pivot->id;
        })
      ],
      'weight' => $this->when(Auth::user()->isAdmin(), $this->whenPivotLoaded('chests_items', function () {
        return $this->pivot->weight;
      })),
      'type' => new ItemTypeResource($this->whenLoaded('type')),
      'appearInChest' => $this->when(Auth::user() ? Auth::user()->isAdmin() : false, $this->appear_in_chest),
      'appearInCraft' => $this->when(Auth::user() ? Auth::user()->isAdmin() : false, $this->appear_in_craft),
      'chests' => ChestResource::collection($this->whenLoaded('chests'))
    ];
  }
}
