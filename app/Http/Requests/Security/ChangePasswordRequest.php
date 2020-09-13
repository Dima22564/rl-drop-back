<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
      'currentPassword' => 'required',
      'newPassword' => 'required|min:6',
      'confirmPassword' => 'required|same:newPassword'
    ];
  }

  public function messages() {
    return [
      'currentPassword.required' => 'Password is required',
      'newPassword.required' => 'New password is required',
      'confirm_password.same' => 'Password must match',
      'newPassword.min' => 'Password min length is 6',
      'confirmPassword.required' => 'Please confirm new password',
    ];
  }
}
