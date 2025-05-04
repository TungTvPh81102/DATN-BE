<?php

namespace App\Http\Requests\API\Courses;

use App\Http\Requests\API\Bases\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentCourse extends BaseFormRequest
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
            'category_id' => 'nullable|integer|exists:categories,id',
            'name' => 'nullable|string|max:255',
            'description' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value && str_word_count($value) > 500) {
                        $fail('Mô tả không được quá 500 từ.');
                    }
                },
            ],
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'intro' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) && !($value instanceof \Illuminate\Http\UploadedFile)) {
                        $fail('Trường intro phải là một tệp hợp lệ hoặc đường dẫn video hiện có.');
                    }
                },
                Rule::when(function () {
                    return $this->hasFile('intro');
                }, ['file', 'mimes:mp4,mov,avi,wmv,flv,3gp', 'max:204800']),
            ],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000000',
                Rule::when($this->input('is_free') == 1, fn($rule) => ['in:0'])
            ],
            'price_sale' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000000',
                function ($attribute, $value, $fail) {
                    $price = $this->input('price');
                    $isFree = $this->input('is_free');

                    if ($isFree == true && $value > 0) {
                        $fail('Khoá học miễn phí thì giá giảm phải bằng 0.');
                    }

                    if ($isFree == false && $price > 0) {
                        $maxSalePrice = $price * 0.6;
                        if ($value > $maxSalePrice) {
                            $fail("Số tiền giảm không được cao hơn 60% so với giá gốc (tối đa $maxSalePrice).");
                        }
                        if ($value > $price) {
                            $fail('Số tiền giảm không được lớn hơn giá gốc.');
                        }
                    }
                }
            ],
            'level' => [
                'nullable',
                'string',
                Rule::in([
                    'beginner',
                    'intermediate',
                    'advanced',
                ])
            ],
            'is_free' => 'nullable|in:0,1',
            'visibility' => [
                'nullable',
                'string',
                Rule::in([
                    'public',
                    'private',
                ])
            ],
              'allow_coding_lesson' => 'nullable|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'category_id.integer' => 'ID danh mục phải là số nguyên.',
            'category_id.exists' => 'Không tìm thấy danh mục.',
            'name.string' => 'Tên khoá học phải là chuỗi ký tự.',
            'name.max' => 'Tên khoá học không được vượt quá 500 ký tự.',
            'description.string' => 'Mô tả khoá học phải là chuỗi ký tự.',
            'thumbnail.image' => 'Thumbnail phải là ảnh.',
            'thumbnail.mimes' => 'Thumbnail phải có định dạng jpeg, png, jpg, gif, webp.',
            'thumbnail.max' => 'Thumbnail không được vượt quá 2MB.',
            'price.numeric' => 'Giá khoá học phải là số.',
            'price.min' => 'Giá khoá học phải lớn hơn 0.',
            'price_sale.numeric' => 'Giá giảm phải là số.',
            'price_sale.min' => 'Giá giảm phải lớn hơn 0.',
            'price_sale.function' => 'Số tiền giảm không được nhỏ hơn 30% so với giá gốc.',
            'level.string' => 'Level phải là chuỗi ký tự.',
            'level.in' => 'Level không hợp lệ.',
            'visibility.string' => 'Quyền riêng tư phải là chuỗi ký tự.',
            'visibility.in' => 'Quyền riêng tư không hợp lệ.',
            'is_free.in' => 'Trường này phải là 0 hoặc 1.',
              'allow_coding_lesson.in' => 'Trường này phải là 0 hoặc 1.'
        ];
    }
}
