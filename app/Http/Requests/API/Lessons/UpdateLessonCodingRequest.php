<?php

namespace App\Http\Requests\API\Lessons;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonCodingRequest extends BaseFormRequest
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
            'title' => 'required|string|max:255',
            'instruct' => 'nullable|string',
            'content' => 'nullable|string',
            'language' => 'nullable|string',
            'hints' => 'nullable|array',
            'solution_code' => 'nullable|string',
            'code' => 'nullable|string',
            'student_code' => 'nullable|string',
            'test_case' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Tiêu đề không được để trống',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'instruct.string' => 'Nội dung phải là chuỗi',
            'instruct.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'content.string' => 'Nội dung phải là chuỗi',
            'language.string' => 'Ngôn ngữ lập trình phải là chuỗi',
            'hints.array' => 'Gợi ý phải là mảng',
            'solution_code.string' => 'Mã lý thuyết phải là chuỗi',
            'code.string' => 'Mã lý thuyết phải là chuỗi',
            'student_code.string' => 'Mã lý thuyết phải là chuỗi',
            'test_case.string' => 'Test case phải là chuỗi',
        ];
    }
}
