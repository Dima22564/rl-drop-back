<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'email' => 'required|email',
      'password' => 'required',
    ];
  }

  public function messages()
  {
    return [
      'email.required' => 'Email is required',
      'email.email' => 'Field email must be valid email',
      'password.required' => 'Password is required',
    ];
  }
}
