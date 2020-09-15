<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Chest as ChestResource;
use App\Item;
use App\ItemTypes;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;
use App\Http\Resources\Item as ItemResource;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemController extends Controller
{

  public function loadItemsForChests()
  {
    $items = Item::where('appear_in_chest', 1)->with('type')->get();
//    return $this->sendResponse(ItemResource::collection($items), 'Ok', 200);
    return $this->sendResponse($items, 'Ok', 200);

  }

  public function loadItemsAll()
  {
    $items = Item::with('type:id,type')->get();

    return $this->sendResponse(ItemResource::collection($items), 'Ok', 200);
  }

  public function store(Request $request)
  {
    $type = ItemTypes::find($request->get('type'));
    $item = Item::create($request->all());
    $item->saveImage($request->file('image'));
    $type->items()->save($item);

    return $this->sendResponse(new ItemResource($item), 'Ok', 201);
  }

  public function deleteById(Request $request, $id)
  {
    $result = Item::find($id)->delete();

    return $this->sendResponse($result, 'Ok', 200);
  }
}
