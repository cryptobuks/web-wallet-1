<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class RegisterRequest extends Request
{
  public function authorize()
  {
    return true;
  }
  public function rules()
  {
    return [
      'username' => 'required|unique:users',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:6',
      'is_broker' => 'required'
    ];
  }
}