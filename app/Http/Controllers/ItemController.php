<?php

namespace App\Http\Controllers;

use App\Chest;
use App\Events\CreateNotification;
use App\Http\Resources\Item as ItemResource;
use App\Item;
use App\ItemTypes;
use App\Notification;
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
  private $user;

  public function __construct()
  {
    $this->user = Auth::user();
  }

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
        \auth()->user()->checkNotifications();
        $notification = Notification::create([
          'text_en' => '<span class="white">You</span> have not enough money. You can  ' . '<span class="blue"> make deposit. </span>',
          'text_ru' => '<span class="white">У Вас</span> не достаточно средств. Вы можете  ' . '<span class="blue"> пополнить баланс. </span>',
          'type' => Notification::WARNING,
          'date' => Carbon::now()->format('Y-m-d H:m:s'),
          'user_id' => Auth::user()->id
        ]);

        event(new CreateNotification($notification));
        return $this->sendError('You have not enough money!', '', 400);
      }

      DB::beginTransaction();

      $this->user->changeBalance($item->$price * $progress / 100 * -1);
      $isSendItem = $item->craftItem($progress);

      $this->user->items()->attach($item->id,
        [
          'is_craft' => 1,
          'price' => $item->$price * $progress / 100,
          'platform' => $platform,
//          'sold' => $isSendItem === 0 ? null : 0,
          'craft_fail' => $isSendItem === 0 ? 1 : 0
        ]);

      $notification = Notification::create([
        'text_en' => sprintf("<span class=\"white\"> You</span> begin to craft! <span class=\"blue\">%s -%s</span>", $item->name, $item->$price),
        'text_ru' => sprintf("<span class=\"white\"> Вы</span> начали крафт! <span class=\"blue\">%s -%s</span>", $item->name, $item->$price),
        'type' => Notification::WARNING,
        'date' => Carbon::now()->format('Y-m-d H:m:s'),
        'user_id' => Auth::user()->id
      ]);

      event(new CreateNotification($notification));

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

      if (!$userItem) {
        DB::rollBack();
        return $this->sendError('Something went wrong!', [], 404);
      }

      $this->user->changeBalance($item->$price);

      $notification = Notification::create([
        'text_en' => sprintf("<span class=\"white\"> You</span> sold <span class=\"blue\">%s +%s$</span>", $item->name, (string)$item->$price),
        'text_ru' => sprintf("<span class=\"white\"> Вы</span> продали <span class=\"blue\">%s +%s$</span>", $item->name, (string)$item->$price),
        'type' => Notification::WARNING,
        'date' => Carbon::now()->format('Y-m-d H:m:s'),
        'user_id' => Auth::user()->id
      ]);

      event(new CreateNotification($notification));

      DB::commit();
      return $this->sendResponse((boolean)$userItem, 'Ok', 200);
    } catch (\Exception $e) {
      DB::rollBack();

      return $this->sendError('Something went wrong!', [$e], 404);
    }
  }

}
