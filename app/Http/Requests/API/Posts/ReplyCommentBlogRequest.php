<?php

namespace App\Http\Requests\API\Posts;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class ReplyCommentBlogRequest extends BaseFormRequest
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
            'content' => 'required|string|max:2000'
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'Vui lòng nhập nội dung bình luận',
            'content.max' => 'Bình luận ko được vượt quá 2000 ký tự',
        ];
    }
}
