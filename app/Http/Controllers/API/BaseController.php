<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
  public function sendResponse($result = [], $message = '', $code = 200) {
    $response = [
      'success' => true,
      'data' => $result,
      'message' => $message,
      'statusCode' => $code
    ];

    return response()->json($response, $code);
  }

  public function sendError($error, $messages = [], $code = 404) {
    $response = [
      'success' => false,
      'message' => $error,
    ];

    if (!empty($messages)) {
      $response['data'] = $messages;
    }

    return response()->json($response, $code);
  }
}
