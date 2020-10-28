<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\BaseController as Controller;
use App\Services\Stats as StatsService;
use Illuminate\Support\Facades\Gate;

class ChestController extends Controller
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
    $data = $this->statsService->getIndexData($craft);

    $keys = [];
    $chestsByPlatforms = [];
    $chestsPrice = [];
    foreach ($data['stats'] as $key => $value) {
      array_push($keys, $key);
      $data1 = $this->statsService->buildData($value, $key, 'quantity');
      $data2 = $this->statsService->buildData($value, $key, 'totalSum');

      array_push($chestsByPlatforms, $data1);
      array_push($chestsPrice, $data2);
    }
//
//
    $dates = [];
    $quantity = [];
    foreach ($data['chests'] as $key => $value) {
      array_push($dates, $key);
      array_push($quantity, count($value));
    };

    return $this->sendResponse([
      'dates' => $dates,
      'quantity' => $quantity,
      'chestByPlatforms' => $chestsByPlatforms,
      'chestsPrice' => $chestsPrice,
      'keys' => $keys
    ], 'Ok', 200);
//    return $data;
  }
//
//  public function updateChestsStats($craft)
//  {
//    $data = $this->statsService->updateChestsStats($craft);
//    return $data;
//  }

  public function chestStatsBetweenTime(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $to = $request->get('dateTo');
    $from = $request->get('dateFrom');
    $stats = DB::table('user_chest')
      ->when($to, function ($query, $to) {
        return $query->where('date', '<', Carbon::createFromFormat('Y-m-d', $to));
      })
      ->when($from, function ($query, $from) {
        return $query->where('date', '>', Carbon::createFromFormat('Y-m-d', $from));
      })
      ->get()
      ->groupBy(function ($items) {
        return Carbon::parse($items->date)->format('Y-m-d');
      });
    $dates = [];
    $quantity = [];
    foreach ($stats as $key => $value) {
      array_push($dates, $key);
      array_push($quantity, count($value));
    };

    return $this->sendResponse(['dates' => $dates, 'quantity' => $quantity], 'Ok', 200);
  }
}
