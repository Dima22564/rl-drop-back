<?php

namespace App\Http\Controllers;

use App\Http\Resources\Item as ItemResource;
use App\Item;
use App\ItemTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ItemType as ItemTypeResource;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{
  private $user;

  public function __construct()
  {
    $this->user = Auth::user();
  }

  public function loadCraftItems()
  {
    $craftItems = Item::where('appear_in_craft', 1)->with('type')->get();
    $types = ItemTypes::all();
    return $this->sendResponse([
      'items' => ItemResource::collection($craftItems),
      'types' => ItemTypeResource::collection($types)
    ], 'Ok', 200);
  }

  public function craftItem($id)
  {
    $item = Item::where('id', $id)->first();
    return $this->sendResponse(new ItemResource($item), 'Ok', 200);
  }

  public function play(Request $request)
  {
    try {
      $item = Item::where('id', $request->get('id'))->first();
      $progress = $request->get('progress');
      $platform = $request->get('platform');
      $price = $platform . '_price';

      if (!Gate::allows('check-balance', $item->$price)) {
        return $this->sendError('You have not enough money!', '', 400);
      }

      DB::beginTransaction();

//      $this->user->changeBalance($item->$price * $progress / 100 * -1);
      $isSendItem = $item->craftItem($progress);

      $this->user->items()->attach($item->id,
        [
          'is_craft' => 1,
          'price' => $item->$price * $progress / 100,
          'platform' => $platform,
//          'sold' => $isSendItem === 0 ? null : 0,
          'craft_fail' => $isSendItem === 0 ? 1 : 0
        ]);

      DB::commit();

      return $this->sendResponse((boolean)$isSendItem, 'Ok', 200);
    } catch (\Exception $e) {
      DB::rollBack();

      return $this->sendError('Something went wrong!', [$e], 404);
    }
  }

  public function sell(Request $request)
  {
    try {
      $platform = $request->get('platform');
      $item = Item::where('id', $request->get('id'))->first();
      $price = $request->get('platform') . '_price';
      DB::beginTransaction();

      $userItem = DB::table('user_item')
        ->where('user_id', $this->user->id)
        ->where('item_id', $request->get('id'))
        ->where('sold', 0)
        ->where('platform', $platform)
        ->where(function ($query) {
            $query->where('craft_fail', 0)
              ->orWhere('craft_fail', null);
        })
        ->limit(1)
        ->update(['sold' => 1]);

      $this->user->changeBalance($item->$price);
      if (!$userItem) {
        DB::rollBack();
        return $this->sendError('Something went wrong!', [], 404);
      }

      DB::commit();
      return $this->sendResponse((boolean)$userItem, 'Ok', 200);
    } catch (\Exception $e) {
      DB::rollBack();

      return $this->sendError('Something went wrong!', [$e], 404);
    }
  }

}
