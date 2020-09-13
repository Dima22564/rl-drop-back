<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Auth;
use App\PasswordSecurity;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Item as ItemResource;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\Gate;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        $query->with('type');
      }])
      ->first();
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
      'count' => $count
    ], 'Ok', 200);
  }
}
