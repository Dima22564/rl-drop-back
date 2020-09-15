<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Faq::class, function (Faker $faker) {
  return [
    'title' => $faker->sentence,
    'text' => $faker->text,
    'category' => $faker->randomElement([
      'General Questions',
      'Balance Withdrawal',
      'Premium',
      'Case Battle'
    ])
  ];
});
