<?php

use Illuminate\Database\Seeder;

class ChestsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    factory(\App\Chest::class, 100)->create();

    $items = \App\Item::all();

    \App\Chest::all()->each(function ($chest) use ($items) {
      $weight = rand(1, 100);
      $chest->items()->attach(
        $items->random(rand(1, 100))->pluck('id')->toArray(),
        ['weight' => $weight]
      );
    });
  }
}
