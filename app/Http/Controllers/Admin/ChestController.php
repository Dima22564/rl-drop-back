<?php

namespace App\Http\Controllers\Admin;

use App\Chest;
use App\Http\Controllers\API\BaseController as Controller;
use App\Http\Resources\Chest as ChestResource;
use App\Item;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\User;
use Illuminate\Support\Facades\Gate;

class ChestController extends Controller
{
  public function store(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    Cache::forget('chests');
    $chest = Chest::create($request->all());
    $chest->saveItems(json_decode($request->get('items')));
    $chest->saveImage($request->file('image'));
    $chest->saveBackgroundImage($request->file('backgroundImage'));

    return $this->sendResponse(new ChestResource($chest), 'Ok', 201);
  }

  public function index()
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $chests = Chest::with('items')->get();

    return $this->sendResponse(ChestResource::collection($chests), 'Ok', 200);
  }

  public function deleteById(Request $request, $id)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    Cache::forget('chests');
    $result = Chest::find($id)->delete();

    return $this->sendResponse($result, 'Ok', 200);
  }

  public function update(Request $request, $id)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    Cache::forget('chests');
    $chest = Chest::find($id);
    $chest->update($request->all());

    if (count($request->allFiles()) > 0) {
      $chest->saveImage($request->file('image'));
    }
    $chest->saveItems(json_decode($request->get('items')), true);

    return $this->sendResponse('', 'Ok', 200);
  }

  public function getById($id)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $chest = Chest::with('items')->find($id);

    return $this->sendResponse(new ChestResource($chest), 'Ok', 200);
  }

}
