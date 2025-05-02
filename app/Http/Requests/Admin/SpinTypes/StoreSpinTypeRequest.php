<?php

namespace App\Http\Requests\Admin\SpinTypes;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpinTypeRequest extends FormRequest
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
            'name' => 'required|unique:spin_types,name',
            'display_name' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Tên key không được để trống',
            'name.unique' => 'Tên key đã tồn tại',
            'display_name.required' => 'Tên hiển thị không được để trống'
        ];
    }
}
