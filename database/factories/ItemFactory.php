<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Item::class, function (Faker $faker) {
  return [
    'name' => $faker->word,
    'pc_price' => $faker->numberBetween(10, 150),
    'ps4_price' => $faker->numberBetween(10, 150),
    'xbox_price' => $faker->numberBetween(10, 150),
    'image' => $faker->imageUrl(),
    'appear_in_chest' => $faker->boolean(),
    'appear_in_craft' => $faker->boolean(),
    'type_id' => $faker->numberBetween(1, 9),
    'text' => $faker->sentence,
    'color' => \Illuminate\Support\Facades\DB::table('item_colors')->find($faker->numberBetween(1, 14))->color
  ];
});
