<?php

namespace App\Http\Controllers;

use App\Chest;
use App\Faq;
use App\IndexBottomBanner;
use App\IndexTopBanner;
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
use Tymon\JWTAuth\Facades\JWTAuth;

class ChestController extends Controller
{
  public function chest($id)
  {
    $chest = Chest::with(['items' => function ($query) {
      $query->with('type');
    }])->find($id);
    $items = $chest->items->makeHidden(['type_id', 'pivot'])->groupBy('type.type');

    return $this->sendResponse([
      'chest' => new ChestResource($chest),
      'items' => $items
    ], 'Ok', 200);
  }

  public function index()
  {
    $chests = Cache::remember('chests', 60 * 60, function () {
      $groupedChests = Chest::where('is_case_visible_for_user', 1)
        ->get();
      $chestsCollection = ChestResource::collection($groupedChests);
      return collect($chestsCollection)->groupBy('category');
    });

    $bottomBanner = IndexBottomBanner::where('name', 'bottom-index-banner')->first();
    $topBanner = IndexTopBanner::where('name', 'top-index-banner')->first();

    return $this->sendResponse([
      'chests' => $chests,
      'indexTopBanner' => $topBanner,
      'indexBottomBanner' => $bottomBanner
    ], 'Ok', 200);
  }

  public function openChest(Request $request)
  {
    $user = auth()->user();
    try {
      $chest = Chest::with(['items' => function ($query) {
        $query->where('appear_in_chest', 1);
      }])
        ->where('id', $request->get('id'))
        ->first();
      $platform = $request->get('platform');
      $price = $platform . '_price';

      if (!$chest->$price || $chest->$price == 0) {
        return $this->sendError('Unable to open chest!', '', 400);
      }

      if (!Gate::allows('check-balance', $chest->$price)) {
        return $this->sendError('You have not enough money!', '', 400);
      }

      if ($chest->is_limited && ($chest->max_open <= $chest->current_open)) {
        return $this->sendError('Case limited!', '', 400);
      }

      DB::beginTransaction();
      Cache::forget('chests');
      $chest->addOpen();

      $user->changeBalance($chest->$price * -1);

      $itemsWeights = array_column($chest->items->toArray(), 'pivot');
      $item = Item::chooseRandomItem($itemsWeights);

      $chest->users()->attach(Auth::user()->id, ['date' => Carbon::now()]);
      $chest->winItems()->attach($item->id);

      $user->items()->attach($item->id, [
        'platform' => $platform,
        'price' => $chest->$price
      ]);

      DB::commit();

      return $this->sendResponse(new ItemResource($item), 'Ok', 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->sendError('Something went wrong!', [$e], 500);
    }
  }
}
