<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CreateItemTypeRequest;
use App\Http\Resources\ItemType as ItemTypeResource;
use App\ItemTypes;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;

class ItemTypesController extends Controller
{
  public function index() {
    $itemTypes = ItemTypes::all();

    return $this->sendResponse(ItemTypeResource::collection($itemTypes), 200);
  }

  public function store(CreateItemTypeRequest $request) {
    $type = ItemTypes::create(['type' => $request->get('type')]);

    return $this->sendResponse(new ItemTypeResource($type), 'Ok', 200);
  }
}
