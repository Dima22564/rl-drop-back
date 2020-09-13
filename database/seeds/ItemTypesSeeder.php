<?php

use Illuminate\Database\Seeder;

class ItemTypesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $cats = [
      [
        'type' => 'common',
        'color' => 'transparent'
      ],
      [
        'type' => 'uncommon',
        'color' => '#00bbff'
      ],
      [
        'type' => 'rare',
        'color' => '#4579f5'
      ],
      [
        'type' => 'very rare',
        'color' => '#9c42f5'
      ],
      [
        'type' => 'import',
        'color' => '#ff0000'
      ],
      [
        'type' => 'exotic',
        'color' => '#ffaa00'
      ],
      [
        'type' => 'black market',
        'color' => '#ff00aa'
      ],
      [
        'type' => 'limited',
        'color' => '#ff5e00'
      ],
      [
        'type' => 'premium',
        'color' => '#00ffaa'
      ]
    ];

    \Illuminate\Support\Facades\DB::table('items_types')->insert($cats);
  }
}
