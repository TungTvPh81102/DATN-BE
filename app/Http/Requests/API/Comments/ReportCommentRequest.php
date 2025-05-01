<?php

namespace App\Http\Requests\API\Comments;

use Illuminate\Foundation\Http\FormRequest;

class ReportCommentRequest extends FormRequest
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
            'comment_id'     => 'required|integer|exists:comments,id',
            'report_content' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'comment_id.required' =>'Comment_id là bắt buộc',
            'comment_id.integer'  =>'Comment_id phải là số nguyên',
            'comment_id.exists'   =>'Bình luận không tồn tại',
            'reporter_content.required'=>'Nội dung là bắt buộc',
            'reporter_content.string'=>'Nội dung phải là chuỗi kí tự',
        ];
    }
}
