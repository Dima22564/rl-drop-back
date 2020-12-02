<?php

namespace App\Http\Controllers;

use App\Chest;
use App\Events\CreateNotification;
use App\Http\Resources\Item as ItemResource;
use App\Http\Resources\User as UserResource;
use App\Item;
use App\ItemTypes;
use App\Notification;
use App\User;
use App\Withdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ItemType as ItemTypeResource;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{

  public function loadCraftItems()
  {
    $craftItems = Cache::remember('craftItems', 2 * 60 * 60, function () {
      return Item::where('appear_in_craft', 1)->with('type')->get();;
    });
    $types = Cache::remember('types', 12 * 60 * 60, function () {
      return $types = ItemTypes::all();
    });
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

      \auth()->user()->changeBalance($item->$price * $progress / 100 * -1);
      $isSendItem = $item->craftItem($progress);

      \auth()->user()->items()->attach($item->id,
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

      if (\auth()->user()->id != $request->get('userId')) {
        return $this->sendError([], 'You do not have access!', 400);
      }

      DB::beginTransaction();

      $userItem = DB::table('user_item')
        ->where('user_id', \auth()->user()->id)
        ->where('item_id', $request->get('id'))
        ->where('sold', 0)
        ->where('platform', $platform)
        ->where(function ($query) {
          $query->where('craft_fail', 0)
            ->orWhere('craft_fail', null);
        })
        ->where('withdraw_status', null)
        ->limit(1)
        ->update(['sold' => 1]);

      if (!$userItem) {
        DB::rollBack();
        return $this->sendError('Something went wrong!', [], 404);
      }

      auth()->user()->changeBalance($item->$price);

      $userId = Auth::user()->id;
      $user = new UserResource(User::where('id', $userId)
        ->with(['passwordSecurity', 'customNotifications' => function ($query) {
          $query->orderBy('created_at', 'desc');
        }])->first());

      DB::commit();
      return $this->sendResponse(['user' => $user], 'Ok', 200);
    } catch (\Exception $e) {
      DB::rollBack();

      return $this->sendError('Something went wrong!', [$e], 404);
    }
  }

}
