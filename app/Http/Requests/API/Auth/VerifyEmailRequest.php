<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
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
            'email'    => ['required','email','exists:user,email'],
            'password' => ['required']
        ];
    }

    public function messages()
    {
        return [
            //
            'email.required' => 'Email không được để trống',
            'email.email'    => 'Email không đúng định dạng',
            'email.exists'   => 'Email không tồn tại',

            'password.required'  => 'Mật khẩu không được để trống',

        ];
    }
}