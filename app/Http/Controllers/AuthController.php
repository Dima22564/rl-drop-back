<?php

namespace App\Http\Controllers;

use App\Events\CustomNotification;
use App\Http\Requests\User\LoginUserRequest;
use App\Http\Requests\User\UserCreateRequest;
use App\PasswordSecurity;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController;
//use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\User as UserResource;

class AuthController extends BaseController
{

  public function login(LoginUserRequest $request)
  {
    $loginData = [
      'email' => $request->get('email'),
      'password' => $request->get('password')
    ];
    $token = null;
    if (!$token = JWTAuth::attempt($loginData)) {
      return $this->senderror( 'Unauthorized', [], 401);
    }

    $user = User::where('email', $loginData['email'])->with('passwordSecurity')->first();
    $img2fa = '';
    if ($user->passwordSecurity['google2fa_enable']) {
      $loginData['google2fa_secret'] = $user->passwordSecurity->google2fa_secret;
      $img2faUrl = PasswordSecurity::generate2faUrl($loginData);
      $img2fa = PasswordSecurity::generate2faImg($img2faUrl);
    }

    CustomNotification::dispatch([
      'text' => 'You logged in!',
      'type' => 'primary',
      'room_id' => $user->id
    ]);
//      return new \App\Http\Resources\User($user);
    return $this->respondWithToken($token, $img2fa);
  }

  public function logout(Request $request)
  {
    JWTAuth::invalidate();
    auth()->logout();
    return $this->sendResponse([], 'Successfully logged out', 200);
  }

  public function register(UserCreateRequest $request)
  {
    $user = User::create($request->all());
    $passwordSecurity = PasswordSecurity::create(['user_id' => $user->id]);
    $user->password_security = $passwordSecurity->id;
    $user->cryptPassword($request->get('password'));
    lad($user);
    $user->createRoom($user->id);
    return $this->sendResponse([], 'User created successfully', 200);
  }

  public function refresh()
  {
    return $this->respondWithToken(auth()->refresh());
  }

  protected function respondWithToken($token, $twofaImg = '')
  {
    return $this->sendResponse([
      'access_token' => $token,
      'user' => new UserResource(auth()->user()),
      'loggedIn' => true,
      'twofaImg' => $twofaImg
      ], 'Successfully logged in');
  }
}
