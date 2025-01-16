<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SingupUserRequest extends FormRequest
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
            'name'       => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email'      => ['required', 'email', 'unique:users,email', 'max:255', 'regex:/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'password'   => ['required', 'string', 'min:8', 'max:255', 'regex:/^(?=.*[A-Z])/'],
            'repassword' => ['required', 'min:8', 'same:password'],
        ];
    }
    public function messages()
    {
        return [
            // Tên
            'name.required' => 'Tên là bắt buộc.',
            'name.string'   => 'Định dạng tên không hợp lệ.',
            'name.regex'    => 'Định dạng tên không hợp lệ.',
            'name.min'      => 'Tên phải có ít nhất 2 ký tự',
            'name.max'      => 'Tên không được vượt quá 255 ký tự.',

            // Email
            'email.required' => 'Email là bắt buộc.',
            'email.email'    => 'Định dạng email không hợp lệ.',
            'email.unique'   => 'Email đã tồn tại.',
            'email.max'      => 'Email không được vượt quá 255 ký tự.',
            'email.regex'    => 'Định dạng email không hợp lệ.',

            // Mật khẩu
            'password.required'  => 'Mật khẩu là bắt buộc.',
            'password.string'    => 'Định dạng mật khẩu không hợp lệ.',
            'password.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.max'       => 'Mật khẩu không được vượt quá 255 ký tự.',
            'password.regex'     => 'Mật khẩu phải chứa ít nhất một chữ cái viết hoa.',

            // Repassword
            'repassword.required' => 'Vui lòng xác nhận mật khẩu.',
            'repassword.min'      => 'Xác nhận mật khẩu phải có ít nhất 8 ký tự.',
            'repassword.same' => 'Mật khẩu và xác nhận mật khẩu không khớp.',
        ];
    }
}
