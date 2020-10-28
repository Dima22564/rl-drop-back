<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    $this->call([
      ItemColorsSeeder::class,
      RoleSeeder::class,
      UsersTableSeeder::class,
      ItemTypesSeeder::class,
      ItemSeeder::class,
      ChestsSeeder::class,
      FaqsSeeder::class,
    ]);

//    factory(\App\Chest::class, 200)->create()->each(function ($chest) {
//      $chest->items()->saveMany(factory(\App\Item::class, 10)->make());
//    });
  }
}
