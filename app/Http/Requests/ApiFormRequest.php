<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

abstract class ApiFormRequest extends FormRequest
{
  protected function failedValidation(Validator $validator)
  {
    // все ошибки валидации
    $errors = (new ValidationException($validator))->errors();

    throw new HttpResponseException(response()->json([
      'success' => false,
      'errors' => $errors
    ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
  }

  abstract public function authorize();
  abstract public function rules();
}
