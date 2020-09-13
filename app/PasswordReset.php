<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
  protected $hidden = ['email', 'token'];

  protected $table = 'password_resets';

}
