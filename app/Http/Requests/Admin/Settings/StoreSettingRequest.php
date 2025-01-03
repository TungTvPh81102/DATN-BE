<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
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
            'key' => ['required','string','max:255','unique:settings,key'],
            'value' => ['nullable','string'],
        ];
    }

    /**
     * Get the custom error messages for the validator.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'key.required' => 'Trường key là bắt buộc.',
            'key.string' => 'Trường key phải là chuỗi ký tự.',
            'key.max' => 'Trường key không được vượt quá 255 ký tự.',
            'key.unique' => 'Trường key đã tồn tại trong hệ thống.',
            'value.string' => 'Trường value phải là chuỗi ký tự.',
        ];
    }
}
