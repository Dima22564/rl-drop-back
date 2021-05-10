<?php

namespace App\Http\Controllers;

use App\Chest;
use App\Jobs\SendConfirmLink;
use App\Jobs\SendLinkToUpdateCredentials;
use App\Notification;
use App\User;
use App\Http\Controllers\API\BaseController;
use App\Withdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\PasswordSecurity;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Item as ItemResource;
use App\Http\Resources\Chest as ChestResource;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Jobs\DeleteNotification;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;


class UserController extends BaseController
{

  public function getUser()
  {
    $userId = Auth::user()->id;
    $user = new UserResource(User::where('id', $userId)
      ->with(['passwordSecurity', 'customNotifications' => function ($query) {
        $query->orderBy('created_at', 'desc');
      }])->first());
    if ($user->passwordSecurity->google2fa_enable) {
      $fields = [
        'email' => $user->email,
        'google2fa_secret' => $user->passwordSecurity->google2fa_secret
      ];
      $google2faUrl = PasswordSecurity::generate2faUrl($fields);
      $qrcode_image = PasswordSecurity::generate2faImg($google2faUrl);
      return $this->sendResponse(['user' => $user, 'google2fa_url' => $qrcode_image], 'User fetched successfully', 200);
    }
    return $this->sendResponse(['user' => $user], 'User fetched successfully', 200);
  }

  public function update(Request $request)
  {
    $email = DB::table('reset_tokens')
      ->where('token', $request->get('token'))
      ->where('type', User::UPDATE_CREDENTIALS)
      ->first();

    if (!$email) {
      return $this->sendError('Error, email is wrong!', [], 404);
    }

    if ($email->expires_at < Carbon::now()) {
      return $this->sendError('Token expired!', [], 404);
    }

    DB::beginTransaction();
    $user = User::where('email', $email->email)->first();
    $name = $user->temp_name;
    $email = $user->temp_email;
    $isUserUpdated = $user->update([
      'name' => $name,
      'email' => $email,
      'temp_email' => null,
      'temp_name' => null
    ]);

    DB::table('reset_tokens')
      ->where('token', $request->get('token'))
      ->where('type', User::UPDATE_CREDENTIALS)
      ->delete();
    DB::commit();

    if (!$isUserUpdated) {
      return $this->sendError('Something goes wrong!', [], 404);
    }

    return $this->sendResponse(['user' => $isUserUpdated], 'Profile successfully updated', 202);
  }

  public function sendUpdateLink (Request $request, $id) {
    $user = User::find(Auth::user()->id);
    $token = DB::table('reset_tokens')
      ->where('email', $user->email)
      ->where('type', User::UPDATE_CREDENTIALS)
      ->first();
    if ($token) {
      return $this->sendError('Link sent! Check your email!', [], 404);
    }
    $r = User::query()->where('email', $user->email)->update([
      'temp_name' => $request->get('name'),
      'temp_email' => $request->get('email')
    ]);
    $resetToken = Uuid::uuid4();
    DB::table('reset_tokens')
      ->insert([
        'email' => $user->email,
        'token' => $resetToken,
        'type' => User::UPDATE_CREDENTIALS,
        'created_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addHours(1)
      ]);

    $details = [
      'token' => $resetToken,
      'email' => $user->email,
      'title' => 'Update Name, Email'
    ];

    $emailJob = new SendLinkToUpdateCredentials($details);
    dispatch($emailJob);

    return $this->sendResponse([$r], 'Ok', 200);
  }

  public function getInventory()
  {
//    $items = Cache::remember('inventory', 60, function () {
//      return User::where('id', Auth::user()->id)
//        ->with(['items' => function ($query) {
//          $query
//            ->withTrashed()
//            ->where('craft_fail', 0)
//            ->where('sold', 0)
//            ->orWhere('craft_fail', null)
//            ->with('type');
//        }])
//        ->first();
//    });
    $items = User::where('id', Auth::user()->id)
      ->with(['items' => function ($query) {
        $query
          ->withTrashed()
          ->where('withdraw_status', null)
          ->orWhere('withdraw_status', Withdraw::PENDING)
          ->where(function ($query) {
            $query->where('craft_fail', 0)
              ->where('sold', 0)
              ->orWhere('craft_fail', null);
          })
          ->with('type');
      }])
      ->first();

    $withdrewItems = User::where('id', Auth::user()->id)
      ->with(['items' => function ($query) {
        $query
          ->withTrashed()
          ->where('withdraw_status', Withdraw::SUCCESS)
          ->with('type');
      }])->first();

    if ($items->items->count() === 0) {
      return $this->sendResponse(false, 'No items', 200);
    }

    $inventory = $items->items
      ->groupBy('pivot.sold');
    $count = array_values($inventory[0]
      ->groupBy('pivot.platform')
      ->map(function ($row, $key) {
        return [
          'platform' => $key,
          'count' => $row->count()
        ];
      })->toArray());

    return $this->sendResponse([
      'inventory' => ItemResource::collection($inventory[0]),
      'count' => $count,
      'soldItems' => count($inventory) > 2 ? ItemResource::collection($inventory[1]) : [],
      'withdrewItems' => ItemResource::collection($withdrewItems->items)
    ], 'Ok', 200);
  }

  public function changePhoto(Request $request)
  {
    $user = Auth::user();
    $user->savePhoto($request->file('photo'));

    return $this->sendResponse($user->photo, 'Ok', 202);
  }

  public function getStats()
  {
    $cases = DB::table('user_item')
      ->where('user_id', Auth::user()->id)
      ->get()
      ->sortBy('is_craft')
      ->groupBy('is_craft')
      ->map(function ($row, $key) {
        return [($key === 0) ? 'cases' : 'crafts' => $row->count()];
      });

    $items = DB::table('user_item')
      ->where('user_id', auth()->user()->id)
      ->where(function ($query) {
        $query->where('craft_fail', 0)
          ->orWhere('craft_fail', null);
      })->count();

    $bestItem = DB::table('user_item')
      ->where('user_id', \auth()->user()->id)
      ->orderBy('price', 'DESC')
      ->limit(1)
      ->join('items', 'items.id', '=', 'user_item.item_id')
      ->select(['items.image', 'items.id', 'items.name'])
      ->first();

    $casesCrafts = [];
    foreach ($cases as $key => $value) {
      foreach ($value as $key2 => $value2) {
        $casesCrafts[$key2] = $value2;
      }
    }

    return $this->sendResponse([
      'cases' => (array_key_exists('cases', $casesCrafts)) ? $casesCrafts['cases'] : 0,
      'crafts' => (array_key_exists('crafts', $casesCrafts)) ? $casesCrafts['crafts'] : 0,
      'items' => $items,
      'bestItem' => $bestItem
    ], 'Ok', 200);

  }

  public function readNotification($id)
  {
    DeleteNotification::dispatch($id);
  }

  public function readAllNotifications()
  {
    Notification::query()->where('user_id', \auth()->user()->id)
      ->delete();

    return $this->sendResponse([], '', 204);
  }

  public function links(Request $request)
  {
    Auth::user()->update([
      'steam_link' => $request->get('steamLink'),
      'xbox_link' => $request->get('xboxLink'),
      'ps4_link' => $request->get('ps4Link'),
    ]);

    return $this->sendResponse([], 'Ok', 201);
  }

  public function index($id)
  {
    $cases = DB::table('user_item')
      ->where('user_id', $id)
      ->get()
      ->sortBy('is_craft')
      ->groupBy('is_craft')
      ->map(function ($row, $key) {
        return [($key === 0) ? 'cases' : 'crafts' => $row->count()];
      });

    $items = DB::table('user_item')
      ->where('user_id', $id)
      ->where(function ($query) {
        $query->where('craft_fail', 0)
          ->orWhere('craft_fail', null);
      })->count();

    $bestItem = DB::table('user_item')
      ->where('user_id', $id)
      ->orderBy('price', 'DESC')
      ->limit(1)
      ->join('items', 'items.id', '=', 'user_item.item_id')
      ->select(['items.image', 'items.id', 'items.name'])
      ->first();

    $user = User::query()->where('id', $id)
      ->select(['id', 'name', 'photo'])->first();

    $itemsInventory = User::where('id', $id)
      ->with(['items' => function ($query) {
        $query
          ->withTrashed()
          ->where('craft_fail', 0)
          ->where('sold', 0)
          ->orWhere('craft_fail', null)
          ->with('type');
      }])
      ->first();

    $withdrewItems = User::where('id', $id)
      ->with(['items' => function ($query) {
        $query
          ->withTrashed()
          ->where('withdraw_status', Withdraw::SUCCESS)
          ->with('type');
      }])
      ->first();

    if ($itemsInventory->items->count() === 0) {
      return $this->sendResponse([
        'cases' => 0,
        'crafts' => 0,
        'items' => $items,
        'bestItem' => $bestItem,
        'user' => $user,
        'inventory' => [],
        'count' => [],
        'soldItems' => [],
        'withdrewItems' => $withdrewItems
      ], 'Ok', 200);
    }
    $inventory = $itemsInventory->items
      ->groupBy('pivot.sold');
    $count = array_values($inventory[0]
      ->groupBy('pivot.platform')
      ->map(function ($row, $key) {
        return [
          'platform' => $key,
          'count' => $row->count()
        ];
      })->toArray());

    $casesCrafts = [];
    foreach ($cases as $key => $value) {
      foreach ($value as $key2 => $value2) {
        $casesCrafts[$key2] = $value2;
      }
    }

    $cases = DB::table('user_chest')
      ->where('user_id', $id)
      ->get()
      ->groupBy('chest_id')
      ->sortByDesc(function ($item, $key) {
        return count($item);
      })
      ->map(function ($item, $key) {
        return collect(['chestId' => $key, 'chestCount' => count($item)]);
      })->values()->first();

    $bestCase = Chest::withTrashed()
      ->where('id', $cases->get('chestId'))
      ->select('id', 'name', 'image')
      ->first();


    return $this->sendResponse([
      'cases' => (array_key_exists('cases', $casesCrafts)) ? $casesCrafts['cases'] : 0,
      'crafts' => (array_key_exists('crafts', $casesCrafts)) ? $casesCrafts['crafts'] : 0,
      'items' => $items,
      'bestItem' => $bestItem,
      'bestCase' => new ChestResource($bestCase),
      'user' => $user,
      'inventory' => ItemResource::collection($inventory[0]),
      'soldItems' => count($inventory) > 2 ? ItemResource::collection($inventory[1]) : [],
      'withdrewItems' => ItemResource::collection($withdrewItems->items),
      'count' => $count,
    ], 'Ok', 200);


  }
}
