<?php

use Illuminate\Database\Seeder;

class ItemColorsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $colors = [
      [
        'name' => 'Обычный',
        'color' => '#cac3b8'
      ],
      [
        'name' => 'Черный',
        'color' => '#000000'
      ],
      [
        'name' => 'Белый',
        'color' => '#ffffff'
      ],
      [
        'name' => 'Серый',
        'color' => '#999999'
      ],
      [
        'name' => 'Красный',
        'color' => '#ff6363'
      ],
      [
        'name' => 'Розовый',
        'color' => '#f89eff'
      ],
      [
        'name' => 'Кобальт',
        'color' => '#506ec9'
      ],
      [
        'name' => 'Лазурь',
        'color' => '#63ffff'
      ],
      [
        'name' => 'Коричневый',
        'color' => '#b46f45'
      ],
      [
        'name' => 'Желтый',
        'color' => '#ffff63'
      ],
      [
        'name' => 'Лайм',
        'color' => '#63ff63'
      ],
      [
        'name' => 'Оранжевый',
        'color' => '#ffaa63'
      ],
      [
        'name' => 'Зеленый',
        'color' => '#457337'
      ],
      [
        'name' => 'Пурпурный',
        'color' => '#a862fc'
      ]
    ];
    foreach ($colors as $item) {
      \Illuminate\Support\Facades\DB::table('item_colors')
        ->insert($item);
    }
  }
}
