<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CreateItemTypeRequest;
use App\Http\Resources\ItemType as ItemTypeResource;
use App\ItemTypes;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ItemTypesController extends Controller
{
  public function index() {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $itemTypes = ItemTypes::all();
    $colors = DB::table('item_colors')->get();

    return $this->sendResponse([
      'itemTypes' => ItemTypeResource::collection($itemTypes),
      'itemColors' => $colors
      ], 200);
  }

  public function store(CreateItemTypeRequest $request) {
    if (!auth()->user()->hasAccess([Role::ADMIN_ROLE])) {
      return $this->sendError('Forbidden', [], 403);
    }
    $type = ItemTypes::create(['type' => $request->get('type')]);

    return $this->sendResponse(new ItemTypeResource($type), 'Ok', 201);
  }
}
