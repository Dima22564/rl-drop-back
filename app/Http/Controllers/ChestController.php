<?php

namespace App\Http\Controllers;

use App\Chest;
use App\Faq;
use App\Item;
use App\Notification;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use App\Http\Resources\Chest as ChestResource;
use App\Http\Resources\Item as ItemResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Events\CreateNotification;

class ChestController extends Controller
{
  public function chest($id)
  {
//    TODO: catch error
    $chest = Chest::with(['items' => function ($query) {
      $query->with('type');
    }])->findOrFail($id);
    $items = $chest->items->makeHidden(['type_id', 'pivot'])->groupBy('type.type');

    return $this->sendResponse(['chest' => new ChestResource($chest), 'items' => $items], 'Ok', 200);
  }

  public function index()
  {

    $chests = Cache::remember('chests', 60 * 60, function () {
      $groupedChests = Chest::where('is_case_visible_for_user', 1)
        ->get();
      $chestsCollection = ChestResource::collection($groupedChests);
      return collect($chestsCollection)->groupBy('category');
    });

    return $this->sendResponse([
      'chests' => $chests,
    ], 'Ok', 200);
  }

  public function openChest(Request $request)
  {
    try {
      $chest = Chest::with(['items' => function($query) {
        $query->where('appear_in_chest', 1);
      }])
        ->where('id', $request->get('id'))
        ->first();
      $platform = $request->get('platform');
      $price = $platform . '_price';

      if (!Gate::allows('check-balance', $chest->$price)) {
        return $this->sendError('You have not enough money!', '', 400);
      }


      DB::beginTransaction();

      Auth::user()->changeBalance($chest->$price * -1);

      $itemsWeights = array_column($chest->items->toArray(), 'pivot');
      $item = Item::chooseRandomItem($itemsWeights);

      $chest->users()->attach(Auth::user()->id, ['date' => Carbon::now()]);
      $chest->winItems()->attach($item->id);
      Auth::user()->items()->attach($item->id, [
        'platform' => $platform,
        'price' => $chest->$price
      ]);
      $notification = Notification::create([
        'text_en' => sprintf("<span class=\"white\"> You</span> opened chest! <span class=\"blue\">-%s</span>", (string)$chest->$price),
        'text_ru' => sprintf("<span class=\"white\"> Вы</span> открыли кейс. <span class=\"blue\">-%s</span>", (string)$chest->$price),
        'type' => Notification::SUCCESS,
        'date' => Carbon::now()->format('Y-m-d H:m:s'),
        'user_id' => Auth::user()->id
      ]);

      event(new CreateNotification($notification));
      DB::commit();

//      return $this->sendResponse($item, 'Ok', 200);
      return $this->sendResponse(new ItemResource($item), 'Ok', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->sendError('Something went wrong!', [$e], 500);
    }
  }
}
