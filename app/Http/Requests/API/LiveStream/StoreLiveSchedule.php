<?php

namespace App\Http\Requests\API\LiveStream;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreLiveSchedule extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:1|max:100',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'visibility' => 'required|in:public,private',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tên sự kiện không được để trống',
            'title.min' => 'Tên sự kiện không được để trống',
            'title.max' => 'Tên sự kiện không được vượt quá 100 ký tự',

            'description.string' => 'Mô tả phải là chuỗi văn bản',

            'starts_at.required' => 'Vui lòng chọn ngày giờ cho sự kiện',
            'starts_at.date' => 'Định dạng ngày giờ không hợp lệ',

            'visibility.required' => 'Vui lòng chọn chế độ hiển thị',
            'visibility.in' => 'Chế độ hiển thị không hợp lệ',

            'thumbnail.file' => 'Tệp tải lên phải là file hình ảnh',
            'thumbnail.mimes' => 'Ảnh thu nhỏ phải có định dạng: jpg, jpeg, png',
            'thumbnail.max' => 'Ảnh thu nhỏ không được vượt quá 2MB',
        ];
    }
}
