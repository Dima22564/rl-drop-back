<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\ItemTypes::class, function (Faker $faker) {
  return [
    'type' => $faker->words(['uncommon', 'rare', 'very rare', 'import', 'exotic', 'black market', 'limited'])
  ];
});
