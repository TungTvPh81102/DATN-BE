<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
        
     public function rules(): array
     {
         return [
             //
             'token'    => ['required'],
             'email'    => ['required','email','exists:user,email'],
             'password' => ['required','string','max:255','confirmed','min:8']
         ];
     }
 
     public function messages()
     {
         return [
             //
             'token.required' => 'Token là bắt buộc',
             'email.required' => 'Email không được để trống',
             'email.email'    => 'Email không đúng định dạng',
             'email.exists'   => 'Email không tồn tại',
 
             'password.required'  => 'Mật khẩu không được để trống',
             'password.string'    => 'Mật khẩu phải là chuỗi',
             'password.max'       => 'Mật khẩu không dài quá 255 kí tự',
             'password.min'       => 'Mật khẩu phải có ít nhất 8 kí tự',
             'password.confirmed' => 'Mật khẩu xác nhận không đúng'
 
         ];
     }
}
