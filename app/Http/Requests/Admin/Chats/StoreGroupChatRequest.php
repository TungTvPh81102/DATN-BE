<?php

namespace App\Http\Requests\Admin\Chats;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupChatRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',  // Kiểm tra tên nhóm
            'members' => 'required|array',  // Kiểm tra danh sách thành viên
            'members.*' => 'exists:users,id',  // Kiểm tra từng thành viên có tồn tại trong bảng users
        ];
    }
    public function messages()
    {
        return [
            'name.string' =>'Tên phải là chuỗi',
            'name.max'=> 'Tên không được quá 255 kí tự',
            'members.required' =>'Thành viên là bắt buộc',
            'members.array' =>'Thành viên phải là một mảng',
            'members.*.exists'=>'Thành viên chưa tồn tại',
        ];
    }
}
