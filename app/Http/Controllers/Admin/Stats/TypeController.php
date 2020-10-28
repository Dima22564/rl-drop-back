<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;

class TypeController extends Controller
{
  public function index()
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $userItems = User::has('items')->with(['items' => function($query) {
      $query->with('type');
    }])->get()->toArray();
    $groupedItems = collect(array_column($userItems, 'items'))->collapse()->groupBy('type.type')->toArray();
    $itemsWithCount = [];
    $types = [];
    $colors = [];
//    $itemsWithCount['types'] = [];
//    $itemsWithCount['count'] = [];
//    $itemsWithCount['sum'] = [];
    foreach ($groupedItems as $key => $value) {
      $item = [
        'value' => count($value),
        'name' => $key
      ];
      array_push($colors, $value[0]['type']['color']);
      array_push($itemsWithCount, $item);
      array_push($types, $key);
//      array_push($itemsWithCount['sum'], array_sum(array_column($value, 'pc_price')));
    }

    return $this->sendResponse([
      'graph' => $itemsWithCount,
      'types' => $types,
      'colors' => $colors
    ], 'Ok', 200);
  }
}
