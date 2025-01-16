<?php

namespace App\Http\Requests\API\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'query' => 'required|string|min:3|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'query.required' => 'Vui lòng nhập từ khóa tìm kiếm.',
            'query.string' => 'Từ khóa tìm kiếm không hợp lệ',
            'query.min' => 'Từ khóa tìm kiếm phải có ít nhất 3 ký tự.',
            'query.max' => 'Từ khóa tìm kiếm không được vượt quá 255 ký tự.',
        ];
    }
}
