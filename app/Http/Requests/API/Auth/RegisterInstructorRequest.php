<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterInstructorRequest extends FormRequest
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
