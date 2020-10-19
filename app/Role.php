<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
  protected $table = 'roles';

  const USER_ROLE = 'user';
  const ADMIN_ROLE = 'admin';
  const MESSENGER_ROLE = 'messenger';

  public function users()
  {
    return $this->belongsToMany(User::class, 'user_role');
  }
}
