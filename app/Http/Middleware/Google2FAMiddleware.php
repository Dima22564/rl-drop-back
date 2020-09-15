<?php

namespace App\Http\Middleware;

use App\Support\Google2FAAuthenticator;
use App\User;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use PragmaRX\Google2FA\Google2FA;
use App\Http\Requests\User\LoginUserRequest;

class Google2FAMiddleware
{
  public function handle($request, Closure $next)
  {
//    $authenticator = app(Google2FAAuthenticator::class)->boot($request);
//
//    if ($authenticator->isAuthenticated()) {
//      return $next($request);
//    }
//
//    return $authenticator->makeRequestOneTimePasswordResponse();
    $user = User::where('email', $request->get('email'))->first();
    if (!$user) {
      return response()->json(['error' => ['messages' => ['Invalid credentials']], 'success' => false], 400);
    }

    if (!$user->passwordSecurity->google2fa_enable) {
      return $next($request);
    }

    if (!$request->get('one_time_password')) {
      return response()->json(['data' => ['success' => true, 'google2faEnable' => true]], 200);
    } else {
      $google2fa = new Google2FA();
      $secret = $request->get('one_time_password');
      $valid = $google2fa->verifyKey($user->passwordSecurity->google2fa_secret, $secret, 4);

      if ($valid) {
        return $next($request);
      } else {
        return response()->json(['message' => 'Invalid code. Please try again', 'success' => false, 'invalidCode' => true], 403);
      }
    }


  }
}
