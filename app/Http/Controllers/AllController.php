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
}
