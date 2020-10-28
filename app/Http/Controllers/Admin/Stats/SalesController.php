<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Role;
use App\Services\Stats as StatsService;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\Gate;

class SalesController extends Controller
{
  private $statsService;

  public function __construct(StatsService $statsService)
  {
    $this->statsService = $statsService;
  }

  public function index()
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $items = $this->statsService->getSoldItemsGroupedByPlatforms();
    return $this->sendResponse($items, 'Ok', 200);
  }
}
