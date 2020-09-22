<?php

namespace App\Exceptions;

use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthException extends Exception
{
  /**
   * Render the exception as an HTTP response.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function render($request, $exception)
  {
    if ($exception instanceof JWTException) {
      $message = 'Unauthorized';

      $json = [
        'success' => false,
        'error' => [
          'messages' => $message,
        ],
        'status' => 401
      ];
      return response()->json($json, 401);
    }
  }
}
