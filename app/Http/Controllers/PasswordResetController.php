<?php

namespace App\Http\Controllers;

use App\Events\CreateNotification;
use App\Http\Requests\Security\ChangePasswordRequest;
use App\Jobs\SendConfirmLink;
use App\Notification;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Jobs\SendPasswordResetLink;
use App\PasswordReset;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class PasswordResetController extends BaseController
{
  public function sendPasswordResetLink(Request $request)
  {
    $user = User::where('email', $request->get('email'))->first();
    $email = $request->get('email');
    if (!$user) {
      return $this->sendError('No such user!', [], 404);
    }
    $token = DB::table('reset_tokens')
      ->where('email', $email)
      ->where('type', User::FORGET_PASSWORD)
      ->first();

    if ($token) {
      return $this->sendError('Link already sent!', [], 404);
    }

    $resetToken = Uuid::uuid4();
    DB::table('reset_tokens')
      ->insert([
        'email' => $email,
        'token' => $resetToken,
        'created_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addHours(1),
        'type' => User::FORGET_PASSWORD
      ]);
    $details = [
      'resetToken' => $resetToken,
      'email' => $request->get('email')
    ];
    $emailJob = new SendPasswordResetLink($details);
    dispatch($emailJob);
    return $this->sendResponse([], 'Check your email!', 200);
  }

  public function recoveryPassword(Request $request)
  {
    $token = DB::table('reset_tokens')
      ->where('token', $request->get('token'))
      ->where('type', User::FORGET_PASSWORD)
      ->first();
    if (!$token->email) {
      return $this->sendError('No such email!', [], 404);
    }
    if ($token->expires_at < Carbon::now()) {
      return $this->sendError('Token expired!', [], 404);
    }

    $user = User::where('email', $token->email)->first();
    $user->cryptPassword($request->get('password'));
    DB::table('reset_tokens')
      ->where('token', $request->get('token'))
      ->where('email', $user->email)
      ->where('type', User::FORGET_PASSWORD)
      ->delete();

    return $this->sendResponse([], 'Your password reset!', 200);
  }

  public function updatePassword(Request $request)
  {
    $email = DB::table('reset_tokens')
      ->where('token', $request->get('token'))
      ->where('type', User::UPDATE_PASSWORD)
      ->first();

    if (!$email) {
      return $this->sendError('Error, email is wrong!', [], 404);
    }

    if ($email->expires_at < Carbon::now()) {
      return $this->sendError('Token expired!', [], 404);
    }

    $user = User::where('email', $email->email)->first();
    $password = $user->temp_password;

    DB::beginTransaction();

    $user->savePassword($password);
    User::where('email', $email->email)
      ->update([
        'temp_password' => null
      ]);
    DB::table('reset_tokens')
      ->where('token', $request->get('token'))
      ->where('type', User::UPDATE_PASSWORD)
      ->delete();
    DB::commit();

    return $this->sendResponse([], 'New password saved!', 200);
  }

  public function sendConfirmEmailForUpdatePassword(ChangePasswordRequest $request)
  {
    $user = User::find(Auth::user()->id);
    if (!Hash::check($request->get('currentPassword'), \auth()->user()->password)) {
      return $this->sendError('Error, password is wrong!', [], 404);
    }
    $token = DB::table('reset_tokens')
      ->where('email', $user->email)
      ->where('type', User::UPDATE_PASSWORD)
      ->first();
    if ($token) {
      return $this->sendError('Link sent! Check your email!', [], 404);
    }
    $user->update([
      'temp_password' => Hash::make($request->get('newPassword'))
    ]);
    $resetToken = Uuid::uuid4();
    DB::table('reset_tokens')
      ->insert([
        'email' => $user->email,
        'token' => $resetToken,
        'type' => User::UPDATE_PASSWORD,
        'created_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addHours(1)
      ]);

    $details = [
      'token' => $resetToken,
      'email' => $user->email
    ];

    $emailJob = new SendConfirmLink($details);
    dispatch($emailJob);

    return $this->sendResponse('Check your email to commit changes!', 'Ok', 200);
  }
}
