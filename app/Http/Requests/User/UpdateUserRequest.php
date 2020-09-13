<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
      'email' => ['required', 'email', Rule::unique('users')->ignore($this->route('id'))],
      'name' => 'required',
    ];
  }

  public function messages() {
    return [
      'email.required' => 'Email is required',
      'email.email' => 'Field email must be valid email',
      'email.unique' => 'Email address is already registered',
      'name.required' => 'Name is required',
    ];
  }
}
