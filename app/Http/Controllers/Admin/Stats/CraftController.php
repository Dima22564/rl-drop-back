<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\DB;
use App\Services\Stats as StatsService;
use Illuminate\Support\Facades\Gate;

class CraftController extends Controller
{
  private $statsService;

  public function __construct(StatsService $statsService)
  {
    $this->statsService = $statsService;
  }

  public function index($craft)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $data = $this->statsService->getFilteredItemsForCraft($craft);

    $keys = [];
    $craftedItemsByPlatforms = [];
    $craftedItemsCost = [];
    foreach ($data as $key => $value) {
      array_push($keys, $key);
      $data1 = $this->statsService->buildData($value, $key, 'quantity');
      $data2 = $this->statsService->buildData($value, $key, 'totalSum');

      array_push($craftedItemsByPlatforms, $data1);
      array_push($craftedItemsCost, $data2);
    }
////
////
//    $dates = [];
//    $quantity = [];
//    foreach ($data['chests'] as $key => $value) {
//      array_push($dates, $key);
//      array_push($quantity, count($value));
//    };

    return $this->sendResponse([
      'craftedItemsByPlatforms' => $craftedItemsByPlatforms,
      'craftedItemsCost' => $craftedItemsCost,
      'keys' => $keys
    ], 'Ok', 200);
//    return $data;
  }
}
