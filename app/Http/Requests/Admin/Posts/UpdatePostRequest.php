<?php

namespace App\Http\Requests\Admin\Posts;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
            'categories' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image'],
            'status' => ['required', 'in:draft,pending,published,private'],
            'view' => ['nullable', 'integer', 'min:0'],
            'is_hot' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
    public function messages()
    {
        return [
            'categories.required' => 'Danh mục là bắt buộc.',
            'categories.exists' => 'Danh mục không tồn tại.',

            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.string' => 'Tiêu đề phải là một chuỗi.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',

            'description.string' => 'Mô tả phải là một chuỗi.',

            'content.string' => 'Nội dung phải là một chuỗi.',

            'thumbnail.string' => 'Đường dẫn ảnh phải là một chuỗi.',
            'thumbnail.max' => 'Đường dẫn ảnh không được vượt quá 255 ký tự.',

            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'view.integer' => 'Lượt xem phải là số nguyên.',
            'view.min' => 'Lượt xem không được nhỏ hơn 0.',

            'is_hot.boolean' => 'Trường hot phải là giá trị boolean.',

            'published_at.date' => 'Ngày xuất bản phải là định dạng ngày hợp lệ.',
        ];
    }
}
