<?php

use Illuminate\Database\Seeder;

class FaqsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    factory(\App\Faq::class, 50)->create();
  }
}
