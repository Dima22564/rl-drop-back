<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{
  public function index()
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }

    $users = User::with('roles')->get();
    $roles = Role::all();

    return $this->sendResponse([
      'users' => UserResource::collection($users),
      'roles' => $roles
    ], 'Ok', 200);
  }

  public function addRole(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }

    $role = Role::find($request->get('roleId'));

    if ($role->role === Role::ADMIN_ROLE) {
      DB::table('user_role')
        ->insert([
          ['user_id' => $request->get('userId'),
            'role_id' => $request->get('roleId')],
          ['user_id' => $request->get('userId'),
            'role_id' => Role::where('role', Role::MESSENGER_ROLE)->first()->id]
        ]);
    } else {
      DB::table('user_role')
        ->insert([
          'user_id' => $request->get('userId'),
          'role_id' => $request->get('roleId')
        ]);
    }

    $users = User::with('roles')->get();

    return $this->sendResponse([
      'users' => UserResource::collection($users)
    ], 'Ok', 200);
  }

  public function removeRole(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }

    DB::table('user_role')
      ->where('user_id', $request->get('userId'))
      ->where('role_id', $request->get('roleId'))
      ->limit(1)
      ->delete();

    $users = User::with('roles')->get();

    return $this->sendResponse([
      'users' => UserResource::collection($users)
    ], 'Ok', 200);
  }
}
