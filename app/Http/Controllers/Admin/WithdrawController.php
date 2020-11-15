<?php

namespace App\Http\Controllers\Admin;

use App\Events\CreateNotification;
use App\Item;
use App\Notification;
use App\Role;
use App\Withdraw;
use Carbon\Carbon;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
  public function index()
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE, Role::MESSENGER_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }

    $withdraws = $this->getPendingWithdraws();

    return $this->sendResponse([
      'withdraws' => $withdraws
    ], 'Ok', 200);
  }

  public function take(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE, Role::MESSENGER_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }

    $withdraw = $this->getWithdrawById($request->get('withdrawId'));

    if (!$withdraw) {
      return $this->sendError('Forbidden', [], 400);
    }

    if ($withdraw->messenger_id) {
      return $this->sendError('Withdraw already taken!', [], 400);
    }

    if ($withdraw->status !== Withdraw::PENDING) {
      return $this->sendError('Forbidden', [], 400);
    }

    $withdraw->update([
      'messenger_id' => auth()->user()->id
    ]);

    $withdraws = $this->getPendingWithdraws();

    return $this->sendResponse([
      'withdraws' => $withdraws
    ], 'Ok', 200);
  }

  public function deny(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE, Role::MESSENGER_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }

    $withdraw = $this->getWithdrawById($request->get('withdrawId'));

    if (!$withdraw) {
      return $this->sendError('Forbidden', [], 400);
    }

    if ($withdraw->status !== Withdraw::PENDING) {
      return $this->sendError('Forbidden', [], 400);
    }

    if ($withdraw->messenger_id !== auth()->user()->id) {
      return $this->sendError('Forbidden', [], 400);
    }

    $this->updateWithdrawStatus($withdraw, Withdraw::FORBID);

    $user = User::find($withdraw->user_id);
    $user->checkNotifications();
    $notification = Notification::create([
      'text_en' => sprintf("<span class=\"white\"> Error</span> to withdraw item %s! <span class=\"blue\">%s</span>", Item::find($withdraw->item_id)->name, (string)Withdraw::FORBID),
      'text_ru' => sprintf("<span class=\"white\"> Ошибка</span> вывода предмета %s! <span class=\"blue\">%s</span>", Item::find($withdraw->item_id)->name, (string)Withdraw::FORBID),
      'type' => Notification::WARNING,
      'date' => Carbon::now()->format('Y-m-d H:m:s'),
      'user_id' => $user->id,
    ]);

    event(new CreateNotification($notification));

    $result = $withdraw->update([
      'status' => Withdraw::FORBID
    ]);

    $withdraws = $this->getPendingWithdraws();

    return $this->sendResponse([
      'withdraws' => $withdraws
    ], 'Ok', 200);
  }

  public function withdraw(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE, Role::MESSENGER_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }

    $withdraw = $this->getWithdrawById($request->get('withdrawId'));

    if (!$withdraw) {
      return $this->sendError('Forbidden', [], 400);
    }

    if ($withdraw->status !== Withdraw::PENDING) {
      return $this->sendError('Forbidden', [], 400);
    }

    if ($withdraw->messenger_id !== auth()->user()->id) {
      return $this->sendError('Forbidden', [], 400);
    }

    $this->updateWithdrawStatus($withdraw, Withdraw::SUCCESS);

    $user = User::find($withdraw->user_id);
    $user->checkNotifications();
    $notification = Notification::create([
      'text_en' => sprintf("<span class=\"white\"> You</span> withdraw item %s! <span class=\"blue\">%s</span>", Item::find($withdraw->item_id)->name, (string)Withdraw::SUCCESS),
      'text_ru' => sprintf("<span class=\"white\"> Вы</span> вывели предмет %s! <span class=\"blue\">%s</span>", Item::find($withdraw->item_id)->name, (string)Withdraw::SUCCESS),
      'type' => Notification::SUCCESS,
      'date' => Carbon::now()->format('Y-m-d H:m:s'),
      'user_id' => $user->id,
    ]);

    event(new CreateNotification($notification));


    $result = $withdraw->update([
      'status' => Withdraw::SUCCESS
    ]);

    $withdraws = $this->getPendingWithdraws();

    return $this->sendResponse([
      'withdraws' => $withdraws
    ], 'Ok', 200);
  }

  private function getPendingWithdraws()
  {
    return Withdraw::query()
      ->orderBy('status')
      ->with('user')
      ->join('items', 'withdraws.item_id', '=', 'items.id')
      ->leftJoin('users', function ($query) {
        $query->on('withdraws.messenger_id', '=', 'users.id');
      })
      ->select(['withdraws.*', 'items.id as item_id', 'items.name as item_name', 'users.email as messenger_email'])
      ->get();
  }

  private function getWithdrawById(int $id)
  {
    return Withdraw::query()
      ->where('id', $id)
      ->first();
  }

  private function updateWithdrawStatus($withdraw, string $status) {
    DB::table('user_item')
      ->where('user_id', $withdraw->user_id)
      ->where('item_id', $withdraw->item_id)
      ->where('withdraw_status', Withdraw::PENDING)
      ->limit(1)
      ->update([
        'withdraw_status' => $status
      ]);
  }
}
