<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SigninInstructorRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name'       => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email'      => ['required', 'email', 'unique:users,email', 'max:255', 'regex:/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'password'   => ['required', 'string', 'min:8', 'max:255', 'regex:/^(?=.*[A-Z])/'],
            'repassword' => ['required', 'min:8', 'same:password'],
            'phone' => 'required|unique:profiles,phone|regex:/^0[0-9]{9}$/',
            'address' => 'required|string|max:255|min:10',
            'experience' => 'required|string|max:255',
            'bio' => 'nullable',
            'degree' => 'required|string|max:255',
            'major' => 'required|string|max:255',
            'certificates' => 'required',
            'qa_systems' => 'required',
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

            'phone.required' => 'Vui lòng nhập số điện thoại của bạn',
            'phone.unique' => 'Số điện thoại đã tồn tại',
            'phone.regex' => 'Số điện thoại không đúng định dạng',

            'address.required' => 'Vui lòng nhập địa chỉ của bạn',
            'address.string' => 'Định dạng địa chỉ không hợp lệ',
            'address.max' => 'Địa chỉ tối đa được nhập 255 ký tự',

            'experience.required' => 'Vui lòng cung cấp thông tin kinh nghiệm.',
            'experience.string' => 'Kinh nghiệm nhập sai định dạng',
            'experience.max' => 'Kinh nghiệm được nhập tối đa 255 ký tự',

            'degree.required' => 'Vui lòng nhập bằng cấp.',
            'degree.string' => 'Bằng cấp nhập sai định dạng',
            'degree.max' => 'Bằng cấp được nhập tối đa 255 ký tự',

            'major.required' => 'Vui lòng nhập chuyên ngành.',
            'major.string' => 'chuyên ngành nhập sai định dạng',
            'major.max' => 'chuyên ngành được nhập tối đa 255 ký tự',

            'certificates.required' => 'Vui lòng nhập thông tin chứng chỉ.',

            'qa_systems.required' => 'Vui lòng nhập thông tin QA.',
        ];
    }
}
