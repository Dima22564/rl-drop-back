<?php

namespace App\Http\Controllers\Admin;

use App\Chest;
use App\Http\Controllers\API\BaseController as Controller;
use App\Http\Resources\Chest as ChestResource;
use Illuminate\Http\Request;

class ChestController extends Controller
{
  public function store(Request $request)
  {
    $chest = Chest::create($request->all());
    $chest->saveItems(json_decode($request->get('items')));
    $chest->saveImage($request->file('image'));

    return $this->sendResponse(new ChestResource($chest), 'Ok', 201);
  }

  public function index()
  {
    $chests = Chest::with('items')->get();

    return $this->sendResponse(ChestResource::collection($chests), 'Ok', 200);
  }

  public function deleteById(Request $request, $id)
  {
    $result = Chest::find($id)->delete();

    return $this->sendResponse($result, 'Ok', 200);
  }
}
