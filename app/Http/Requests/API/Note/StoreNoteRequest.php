<?php

namespace App\Http\Requests\API\Note;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
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
            'user_id' => 'required|integer',
            'lesson_id' => 'required|integer',
            'time' => 'required|integer',
            'content'=>'required|max:2000',
        ];
    }
    public function messages()
    {
        return [
            'user_id.required' => 'user_id là bắt buộc',
            'user_id.integer' => 'user_id phải là số nguyên',
            'lesson_id.required'=>'lesson_id là bắt buộc',
            'lesson_id.integer' => 'lesson_id phải là số nguyên',
            'time.required' => 'time là bắt buộc',
            'time.integer' => 'time phải là số nguyên',
            'content.required' => 'content là bắt buộc',
            'content.max'=>'content không được vượt quá 2000 kí tự'
        ];
    }
}
