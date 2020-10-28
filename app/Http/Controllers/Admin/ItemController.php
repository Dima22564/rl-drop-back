<?php

namespace App\Http\Controllers\Admin;

use App\Chest;
use App\Http\Resources\Chest as ChestResource;
use App\Item;
use App\ItemTypes;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\API\BaseController as Controller;
use App\Http\Resources\Item as ItemResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{

  public function loadItemsForChests()
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $items = Item::where('appear_in_chest', 1)->with('type')->get();
//    return $this->sendResponse(ItemResource::collection($items), 'Ok', 200);
    return $this->sendResponse($items, 'Ok', 200);
  }

  public function loadItemsAll()
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $items = Item::with('type:id,type')->get();

    return $this->sendResponse(ItemResource::collection($items), 'Ok', 200);
  }

  public function store(Request $request)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    Cache::forget('craftItems');
    $type = ItemTypes::find($request->get('type'));
    $item = Item::create($request->all());
    $item->saveImage($request->file('image'));
    $type->items()->save($item);

    return $this->sendResponse(new ItemResource($item), 'Ok', 201);
  }

  public function deleteById(Request $request, $id)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    Cache::forget('craftItems');
    $result = Item::find($id)->delete();

    return $this->sendResponse($result, 'Ok', 200);
  }

  public function getById($id)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $item = Item::with('type')->find($id);

    return $this->sendResponse(new ItemResource($item), 'Ok', 200);
  }

  public function update(Request $request, $id)
  {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    Cache::forget('craftItems');
    $item = Item::find($id);
    $item->update($request->all());

    if (count($request->allFiles()) > 0) {
      $item->saveImage($request->file('image'));
    }

    return $this->sendResponse('', 'Ok', 204);
  }
}
