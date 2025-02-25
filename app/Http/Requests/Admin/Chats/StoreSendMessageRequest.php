<?php

namespace App\Http\Requests\Admin\Chats;

use Illuminate\Foundation\Http\FormRequest;

class StoreSendMessageRequest extends FormRequest
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
<<<<<<< HEAD
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:255',
            'type' => 'nullable|string',
            'parent_id' => 'nullable|exists:messages,id',
            'meta_data' => 'nullable|json',
=======
        'conversation_id' => 'required|exists:conversations,id',
        'content' => 'required|string|max:255',
        'type' => 'nullable|string',  
        'parent_id' => 'nullable|exists:messages,id',  
        'meta_data' => 'nullable|json',
        'input_file' =>'nullable|file' 
>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
        ];
    }
    public function messages()
    {
        return [
<<<<<<< HEAD
            'conversation_id.required' => 'Id cuộc trò chuyện là bắt buộc',
            'conversation_id.exists' => 'Không tồn tại cuộc trò chuyện',
            'content.required' => 'Nội dung là bắt buộc',
            'content.string' => 'Nội dung phải là chuỗi kí tự ',
            'content.max' => 'Nội dung tối đa 255 kí tự',
            'type.string' => 'Kiểu phải là chuỗi kí tự',
            'parent_id.exists' => 'Tin nhắn cha k tồn tại trong hệ thống'
=======
            'conversation_id.required' =>'Id cuộc trò chuyện là bắt buộc',
            'conversation_id.exists' =>'Không tồn tại cuộc trò chuyện',
            'content.required' =>'Nội dung là bắt buộc',
            'content.string' =>'Nội dung phải là chuỗi kí tự ',
            'content.max' =>'Nội dung tối đa 255 kí tự',
            'type.string' =>'Kiểu phải là chuỗi kí tự',
            'parent_id.exists'=>'Tin nhắn cha k tồn tại trong hệ thống',
            'input_file' =>'Định dạng phải là file'
>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
        ];
    }
}
