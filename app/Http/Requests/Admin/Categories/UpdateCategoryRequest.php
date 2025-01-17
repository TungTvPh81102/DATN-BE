<?php

namespace App\Http\Requests\Admin\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $segment = $this->segment(3);
        // dd(request('icon_url'), $this->input('icon_url'));
        return [
            //
            'name'        => ['required','string','max:255'],
            'icon'        => 'nullable|image|max:2048',
            'status'      => [ Rule::in([0,1])],
        ];

    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên không được để trống',
            'name.string'   => 'Tên phải là một chuỗi',
            'name.max'      => 'Tên không được quá 255 kí tự',

            'slug.required' => 'Url không được để trống',
            'icon.required' => 'Icon không được để trống',
            'slug.unique'   => 'Url đã tồn tại, Vui lòng nhập lại',
        ];
    }
}
