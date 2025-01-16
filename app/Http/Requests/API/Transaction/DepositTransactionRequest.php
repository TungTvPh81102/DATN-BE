<?php

namespace App\Http\Requests\API\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class DepositTransactionRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:10000', 'regex:/^\d{1,8}(\.\d{1,2})?$/']
        ];
    }
    public function messages(): array
    {
        return [
            'amount.required' => 'Vui lòng nhập số tiền cần nạp',
            'amount.numeric' => 'Định dạng số tiền nạp không đúng',
            'amount.regex' => 'Số tiền nạp tối đa là 10 số bao gồm cả số thập phân',
            'amount.min' => 'Số tiền nạp tối thiểu là 10.000 đồng',
        ];
    }
}
