<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Faq::class, function (Faker $faker) {
  return [
    'title_ru' => $faker->sentence,
    'title_en' => $faker->sentence,
    'text_ru' => $faker->text,
    'text_en' => $faker->text,
    'category_en' => $faker->randomElement([
      'General Questions',
      'Balance Withdrawal',
      'Premium',
      'Case Battle'
    ]),
    'category_ru' => $faker->randomElement([
      'Общие',
      'Баланс',
      'Премиум',
      'Кейс'
    ]),
  ];
});
