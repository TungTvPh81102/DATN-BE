<?php

namespace App\Http\Requests\Admin\Coupons;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:coupons,code',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric',
            'start_date' => 'required|date',
            'expire_date' => 'required|date',
            'used_count' => 'required|integer',
            'status' => 'boolean',
        ];
    }
   
     /**
     * Get the custom error messages for the validator.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //User
            'user_id.required' => 'Trường user_id là bắt buộc.',
            'user_id.exists' => 'Trường user_id không tồn tại.',

            //Name
            'name.required' => 'Trường name là bắt buộc.',
            'name.string' => 'Trường name phải là chuỗi ký tự.',
            'name.max'=>'Trường name vượt quá 255 kí tự',

            //Code
            'code.required' => 'Trường code là bắt buộc.',
            'code.string' => 'Trường code phải là chuỗi ký tự.',
            'code.max'=>'Trường code vượt quá 255 kí tự',
            'code.unique' => 'Trường code đã tồn tại trong hệ thống.',

            //Discount_type
            'discount_type.required' => 'Trường discount_type là bắt buộc.',
            'discount_type.in' => 'Trường discount_type không hợp lệ.',

            //Discount_value
            'discount_value.required' => 'Trường discount_value là bắt buộc.',
            'discount_value.numeric' => 'Trường discount_value phải là số.',
            
            //Description
            'description.string' => 'Trường description phải là chuỗi ký tự.',
           
            //Start_date
            'start_date.required'=>'Trường start_date là bắt buộc',
            'start_date.date'=>'Sai kiểu dữ liệu date',

             //Expire_date
             'expire_date.required'=>'Trường expire_date là bắt buộc',
             'expire_date.date'=>'Sai kiểu dữ liệu date',

             //Used_count
             'used_count.required'=>'Trường used_count là bắt buộc',
             'used_count.integer'=>'Sai kiểu dữ liệu interger',
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}