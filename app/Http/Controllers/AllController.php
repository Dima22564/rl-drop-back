<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\DB;

class AllController extends Controller
{
  public function index()
  {
    $crafts = DB::table('user_chest')
      ->count();
    $cases = DB::table('user_item')
      ->where('craft_fail', 1)
      ->orWhere('craft_fail', 0)
      ->count();
    $users = DB::table('users')
      ->count();

    return $this->sendResponse([
      'users' => $users,
      'crafts' => $crafts,
      'cases' => $cases
    ], 'Ok', 200);
  }

  public function winItems()
  {
    $items = DB::table('user_item')
      ->where('is_craft', 0)
      ->orderBy('id', 'DESC')
      ->join('items', 'items.id', '=', 'user_item.item_id')
      ->join('items_types', 'items.type_id', '=', 'items_types.id')
      ->select([
        'user_id as userId',
        'items.color as color',
        'items.image as image',
        'items_types.color as type_color',
        'items_types.id as type_id',
        'items_types.type as type_type',
        'items.name as name',
        'items.id as id',
        'items.pc_price as pc_price',
        'items.ps4_price as ps4_price',
        'items.xbox_price as xbox_price'
      ])
      ->take(40)
      ->get();

    $serItems = [];

    foreach ($items as $key => $i) {
      $item = [];
      $item['userId'] = $i->userId;

      $type = [];
      $type['id'] = $i->type_id;
      $type['color'] = $i->type_color;
      $type['type'] = $i->type_type;

      $item['key'] = $key;
      $item['winItem']['id'] = $i->id;
      $item['winItem']['type'] = $type;
      $item['winItem']['color'] = $i->color;
      $item['winItem']['image'] = $i->image;
      $item['winItem']['pcPrice'] = $i->pc_price;
      $item['winItem']['ps4Price'] = $i->ps4_price;
      $item['winItem']['xboxPrice'] = $i->xbox_price;
      $item['winItem']['name'] = $i->name;

      array_push($serItems, $item);
    }

    return $serItems;
  }
}
