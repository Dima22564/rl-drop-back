<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\PasswordSecurity;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Item as ItemResource;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\File;

class UserController extends BaseController
{
  public function __construct()
  {
//    $this->user = JWTAuth::parseToken()->authenticate();
  }

  public function getUser()
  {
    $userId = Auth::user()->id;
    $user = new UserResource(User::where('id', $userId)->with('passwordSecurity')->first());
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

  public function update(UpdateUserRequest $request, $id)
  {
    if (!Gate::allows('user-update', $id)) {
      return $this->sendError('You cannot update profile!', [], 403);
    }
    $isUserUpdated = auth::user()->update([
      'name' => $request->get('name'),
      'email' => $request->get('email'),
      'phone_number' => $request->get('phone_number')
    ]);

    if (!$isUserUpdated) {
      return $this->sendError('Something goes wrong!', [], 404);
    }

    return $this->sendResponse(['user' => $isUserUpdated], 'Profile successfully updated', 200);
  }

  public function getInventory()
  {
    $items = User::where('id', Auth::user()->id)
      ->with(['items' => function ($query) {
        $query
          ->where('craft_fail', 0)
          ->orWhere('craft_fail', null)
          ->where('sold', 0)
          ->with('type');
      }])
      ->first();

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
    ], 'Ok', 200);
  }

  public function changePhoto(Request $request)
  {
    $user = Auth::user();
    $user->savePhoto($request->file('photo'));
//    Storage::delete('uploads/users/' . (string)$user->id . '.png');

//    return unlink(storage_path('app/public/uploads/users/' . (string)$user->id . '.png'));
//    return (string)$user->id;
//    return file_exists(storage_path('app/public/uploads/users/12.png'));

    return $this->sendResponse($user->photo, 'Ok', 200);
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

    return $this->sendResponse([
      'cases' => ($cases->count() > 0) ? $cases[0]['cases'] : 0,
      'crafts' => ($cases->count() > 0) ? $cases[1]['crafts'] : 0,
      'items' => $items
    ], 'Ok', 200);


  }
}
