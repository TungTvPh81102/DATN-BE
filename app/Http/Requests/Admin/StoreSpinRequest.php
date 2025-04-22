<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpinRequest extends FormRequest
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
            'type' => ['required',Rule::unique('spin_configs')],
            'cells'=>'required|numeric',
            'probability' => 'required|numeric|min:0|max:100',
        ];
    }
    public function messages(): array
    {
        return [
            'type.required' => 'Loại quà không được để trống',
            'type.unique'   => 'Loại quà đã tồn tại',
            'cells.required'     => 'Số ô quà không được để trống',
            'cells.numeric'     => 'Số ô quà phải là số',
            'probability.required'     => 'Tỉ lệ trúng không được để trống',
            'probability.numeric'     => 'Tỉ lệ trúng phải là số',
            'probability.max:100'     => 'Tỉ lệ trúng tối đa là 100%',
        ];
    }
}
