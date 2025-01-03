<?php

namespace App\Http\Requests\Admin\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name'        => 'required|max:255',
            'slug'        => 'required|max:255|unique:categories',
            'icon'        => 'required',
            'status'      => [Rule::in(0,1)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên không được để trống',
            'slug.required' => 'Url không được để trống',
            'icon.required' => 'Icon không được để trống',
            'slug.unique' => 'Url đã tồn tại, Vui lòng nhập lại',
        ];
    }
}
