<?php

namespace App\Http\Requests\API\Lessons;

use Illuminate\Foundation\Http\FormRequest;

class MoveLessonsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'slug' => 'required|exists:courses,slug',
            'sourceChapterId' => 'required|integer|exists:chapters,id',
            'targetChapterId' => 'required|integer|exists:chapters,id|different:sourceChapterId',
            'lessonIds' => 'required|array|min:1',
            'lessonIds.*' => 'required|integer|exists:lessons,id',
            'preserveOrder' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'slug.required' => 'Không tìm thấy khoá học',
            'sourceChapterId.required' => 'Chương nguồn là bắt buộc',
            'sourceChapterId.integer' => 'Chương nguồn không hợp lệ',
            'sourceChapterId.exists' => 'Chương nguồn không tồn tại',
            'targetChapterId.required' => 'Chương đích là bắt buộc',
            'targetChapterId.integer' => 'Chương đích không hợp lệ',
            'targetChapterId.exists' => 'Chương đích không tồn tại',
            'targetChapterId.different' => 'Chương đích phải khác chương nguồn',
            'lessonIds.required' => 'Vui lòng chọn ít nhất một bài học',
            'lessonIds.array' => 'Danh sách bài học không hợp lệ',
            'lessonIds.min' => 'Vui lòng chọn ít nhất một bài học',
            'lessonIds.*.required' => 'ID bài học là bắt buộc',
            'lessonIds.*.integer' => 'ID bài học không hợp lệ',
            'lessonIds.*.exists' => 'Một số bài học không tồn tại',
        ];
    }
}
