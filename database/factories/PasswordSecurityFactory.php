<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PasswordSecurity;
use Faker\Generator as Faker;

$factory->define(PasswordSecurity::class, function (Faker $faker) {
  return [
    'google2fa_enable' => 0,
    'google2fa_secret' => null,
//    'user_id' => function() {
//      return factory(App\User::class)->create()->id;
//    },
  ];
});
