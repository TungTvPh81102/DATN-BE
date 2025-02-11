<?php

namespace App\Http\Requests\Admin\Roles;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:roles,name',
            // 'guard_name' => 'required|in:web,api',
            'description' => 'nullable|string|max:255',
        ];
    }


    public function messages()
    {
        return [
            'name.required' => 'Vai trò không được để trống',
            'name.unique' => 'Vai trò đã tồn tại',
            'description.max' => 'Mô tả không được dài quá 255 ký tự',
            // 'guard_name.required' => 'Guard name không được để trống',
            // 'guard_name.in' => 'Guard name không hợp lệ',
        ];
    }
}
