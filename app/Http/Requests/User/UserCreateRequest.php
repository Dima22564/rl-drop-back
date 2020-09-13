<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

class UserCreateRequest extends ApiFormRequest
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
      'email' => 'required|email|unique:users,email',
      'name' => 'required',
      'password' => 'required|min:6',
      'confirm_password' => 'required|same:password',
      'accept_policy' => 'accepted'
    ];
  }

  public function messages() {
    return [
      'email.required' => 'Email is required',
      'email.email' => 'Field email must be valid email',
      'email.unique' => 'Email address is already registered',
      'name.required' => 'Name is required',
      'password.required' => 'Password is required',
      'password.min' => 'Password min length is 6',
      'confirm_password.required' => 'Confirm password is required',
      'confirm_password.same' => 'Passwords must match',
      'accept_policy.accepted' => 'Please accept Terms of Service'
    ];
  }
}
