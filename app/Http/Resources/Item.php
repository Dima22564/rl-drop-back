<?php

namespace App\Http\Resources;

use App\Role;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Chest as ChestResource;
use App\Http\Resources\ItemType as ItemTypeResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
        }),
        'withdrawStatus' => $this->whenPivotLoaded('user_item', function () {
          return $this->pivot->withdraw_status;
        })
      ],
      'weight' => $this->when(Auth::user() ? auth()->user()->hasAccess([Role::ADMIN_ROLE]) : false, $this->whenPivotLoaded('chests_items', function () {
        return $this->pivot->weight;
      })),
      'text' => $this->text,
      'color' => $this->color,
      'type' => new ItemTypeResource($this->whenLoaded('type')),
      'appearInChest' => $this->when(Auth::user() ? auth()->user()->hasAccess([Role::ADMIN_ROLE]) : false, $this->appear_in_chest),
      'appearInCraft' => $this->when(Auth::user() ? auth()->user()->hasAccess([Role::ADMIN_ROLE]) : false, $this->appear_in_craft),
      'chests' => ChestResource::collection($this->whenLoaded('chests'))
    ];
  }
}
