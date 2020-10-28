<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Chest::class, function (Faker $faker) {
  return [
    'name' => $faker->word,
    'is_case_visible_for_user' => $faker->boolean(),
    'image' => $faker->imageUrl('255', '216'),
    'old_price' => $faker->numberBetween(10, 100),
    'pc_price' => $faker->numberBetween(10, 100),
    'xbox_price' => $faker->numberBetween(10, 100),
    'ps4_price' => $faker->numberBetween(10, 100),
    'category' => $faker->word
  ];
});
