<?php

namespace App\Http\Controllers;

use App\Events\CreateNotification;
use App\Http\Requests\Security\ChangePasswordRequest;
use App\Notification;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Jobs\SendPasswordResetLink;
use App\PasswordReset;
use Tymon\JWTAuth\Facades\JWTAuth;

class PasswordResetController extends BaseController
{

  private $user;

  public function __construct()
  {
    $this->user = Auth::user();
  }

  public function sendPasswordResetLink(Request $request)
  {
    $user = User::where('email', $request->get('email'))->first();
    if (!$user) {
      return $this->sendError('No such user!', [], 404);
    }
    PasswordReset::where('email', $request->get('email'))->delete();

    $resetToken = Str::random(100);
    $passwordReset = new PasswordReset();
    $passwordReset->email = $request->get('email');
    $passwordReset->token = $resetToken;
    $passwordReset->save();
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
//    TODO Make PasswordResetRequest
    $passwordResetCell = PasswordReset::where('token', $request->get('token'))->first();

    if (!$passwordResetCell) {
      return $this->sendError([], 'No such email!', 404);
    }

    if (!$passwordResetCell->created_at->addHours(1)->greaterThan(Carbon::now())) {
      $passwordResetCell->delete();
      return $this->sendError([], 'Token expired!', 400);
    }

    $user = User::where('email', $passwordResetCell->email)->first();
    $passwordResetCell->delete();
    $user->cryptPassword($request->get('password'));

    $user->checkNotifications();
    $notification = Notification::create([
      'text_en' => sprintf("<span class=\"white\"> You</span> changed your password!"),
      'text_ru' => sprintf("<span class=\"white\"> Вы</span> сменили пароль!"),
      'type' => Notification::WARNING,
      'date' => Carbon::now()->format('Y-m-d H:m:s'),
      'user_id' => Auth::user()->id
    ]);

    event(new CreateNotification($notification));

    return $this->sendResponse([], 'Your password reset!', 200);
  }

  public function updatePassword(ChangePasswordRequest $request)
  {
    if (!Hash::check($request->get('currentPassword'), $this->user->password)) {
      return $this->sendError('Error, password is wrong!', [], 404);
    }

    $this->user->cryptPassword($request->get('newPassword'));

    return $this->sendResponse([], 'New password saved!', 200);
  }
}
