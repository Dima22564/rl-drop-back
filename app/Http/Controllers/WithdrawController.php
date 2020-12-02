<?php

namespace App\Http\Controllers;

use App\Events\CreateNotification;
use App\Notification;
use App\Withdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\BaseController as Controller;

class WithdrawController extends Controller
{
  public function withdraw(Request $request)
  {
    $user = Auth::user();
    if ($user->id !== $request->get('userId')) {
      return $this->sendError([], 'You do not have access!', 400);
    }
    DB::beginTransaction();
    $item = DB::table('user_item')
      ->where('user_id', $user->id)
      ->where('item_id', $request->get('itemId'))
      ->where('sold', '0')
      ->where(function ($query) {
        $query->where('craft_fail', 0)
          ->orWhere('craft_fail', null);
      })
      ->where('withdraw_status', null)
      ->join('items', 'user_item.item_id', '=', 'items.id')
      ->select(['items.id as item_id'])
      ->first();

    $update = DB::table('user_item')
      ->where('user_id', $user->id)
      ->where('item_id', $request->get('itemId'))
      ->where('sold', '0')
      ->where(function ($query) {
        $query->where('craft_fail', 0)
          ->orWhere('craft_fail', null);
      })
      ->limit(1)
      ->update([
        'withdraw_status' => Withdraw::PENDING
      ]);

    if (!$item) {
      DB::rollBack();
      return $this->sendError('Error to withdraw!', [], 400);
    }

    $user->checkNotifications();
    $notification = Notification::create([
      'text_en' => sprintf("<span class=\"white\"> You</span> withdraw item! <span class=\"blue\">Status is %s</span>", (string)Withdraw::PENDING),
      'text_ru' => sprintf("<span class=\"white\"> Вы</span> выводите предмет! <span class=\"blue\">Статус %s</span>", (string)Withdraw::PENDING),
      'type' => Notification::MESSAGE,
      'date' => Carbon::now()->format('Y-m-d H:m:s'),
      'user_id' => $user->id
    ]);
    event(new CreateNotification($notification));

    DB::table('withdraws')
      ->insert([
        'status' => Withdraw::PENDING,
        'item_id' => $item->item_id,
        'platform' => $request->get('platform'),
        'user_id' => $user->id,
        'created_at' => Carbon::now()
      ]);
    DB::commit();

    return $this->sendResponse([], 'Withdrawing is in process!', 200);
  }
}
