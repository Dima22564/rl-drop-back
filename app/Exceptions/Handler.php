<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWT;

class Handler extends ExceptionHandler
{
  /**
   * A list of the exception types that are not reported.
   *
   * @var array
   */
  protected $dontReport = [
    //
  ];

  /**
   * A list of the inputs that are never flashed for validation exceptions.
   *
   * @var array
   */
  protected $dontFlash = [
    'password',
    'password_confirmation',
  ];

  /**
   * Report or log an exception.
   *
   * @param \Throwable $exception
   * @return void
   *
   * @throws \Exception
   */
  public function report(Throwable $exception)
  {
    parent::report($exception);
  }

  public function render($request, \Throwable $exception)
  {
    if ($request->ajax() || $request->wantsJson()) {
      // если это не ошибка валидации, то пишем сообщение, полученное из экземпляра исключения
      $message = [$exception->getMessage()];

      // если ошибка валидации
      if ($exception instanceof ValidationException) {
        $message = $exception->errors();
      }

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

      $json = [
        'success' => false,
        'error' => [
          'messages' => $message,
        ],
      ];
      // возвращаем массив ошибок
      return response()->json($json, 400);
    }

    return parent::render($request, $exception);
  }

}
