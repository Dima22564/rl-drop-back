<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Endroid\QrCode\QrCode;
use PragmaRX\Google2FA\Google2FA;

class PasswordSecurity extends Model
{
  protected $fillable = ['user_id'];

  public function user()
  {
    return $this->belongsTo('App\User');
  }

  static public function generate2faImg ($google2faUrl) {
    $qrCode = new QrCode($google2faUrl);
    $qrCode->setSize(168);
    $qrCode->setMargin(0);

    return $qrCode->writeDataUri();
  }

  static public function generate2faUrl($fields) {
    $google2fa = new Google2FA();
    $google2fa->setWindow(2);
    return $google2fa->getQRCodeUrl(
      'Rl Drop',
      $fields['email'],
      $fields['google2fa_secret']
    );
  }
}
