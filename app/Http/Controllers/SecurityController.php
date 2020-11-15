<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\Security\UpdatePasswordRequest;

use App\PasswordSecurity;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Tymon\JWTAuth\Facades\JWTAuth;

class SecurityController extends BaseController
{
  public function disable2fa(Request $request) {
    $fetchedUser = auth::user();
    if (!$fetchedUser->passwordSecurity->google2fa_enable) {
      return $this->sendResponse([], '2fa already deactivated!', 200);
    }

    $fetchedUser->passwordSecurity()->update([
      'google2fa_enable' => 0,
      'google2fa_secret' => null
    ]);

    return $this->sendResponse([], '2fa successfully deactivated!', 200);
  }

  public function enable2fa(Request $request)
  {
    // Initialise the 2FA class
    $google2fa = new Google2FA();
    $fetchedUser = auth::user();
    if ($fetchedUser->passwordSecurity->google2fa_enable) {
      return $this->sendResponse([], 'Secret key already generated!', 200);
    }

//     Add the secret key to the registration data
    $fetchedUser->passwordSecurity->google2fa_enable = 1;
    $fetchedUser->passwordSecurity->google2fa_secret = $google2fa->generateSecretKey();
    $fetchedUser->passwordSecurity->save();
//    $fetchedUser->passwordSecurity->update([
//      'google2fa_enable' => 1,
//      'google2fa_secret' => $google2fa->generateSecretKey()
//    ]);
    $data = $this->generate2fa($fetchedUser);

    return $this->sendResponse($data, 'Secret key generated successfully', 201);
  }

  protected function generate2fa($user)
  {
    $fields = [
      'email' => $user->email,
      'google2fa_secret' => $user->passwordSecurity->google2fa_secret
    ];
    $google2faUrl = PasswordSecurity::generate2faUrl($fields);
    $qrcode_image = PasswordSecurity::generate2faImg($google2faUrl);
    return array(
      'user' => $user,
      'google2fa_url' => $qrcode_image
    );
  }
}
