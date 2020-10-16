<?php

namespace App\Http\Controllers;

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
    $item = DB::table('user_item')
      ->where('user_id', Auth::user()->id)
      ->where('item_id', $request->get('item_id'))
      ->where('sold', '0')
      ->where(function ($query) {
        $query->where('craft_fail', 0)
          ->orWhere('craft_fail', null);
      })
      ->where('is_withdraw', 0)
      ->first();

    if (!$item) {
      return $this->sendError('Error to withdraw!', [], 400);
    }

    DB::table('withdraws')
      ->insert([
        'status' => Withdraw::PENDING,
        'item_id' => $item->id,
        'user_id' => Auth::user()->id,
        'created_at' => Carbon::now()
      ]);

    return $this->sendResponse([], 'Withdrawing is in process!', 200);
  }
}
