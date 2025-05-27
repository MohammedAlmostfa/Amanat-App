<?php

namespace App\Http\Requests\CustomerDebetRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateCustomerDebetData extends FormRequest
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
            'amount_due' => 'nullable|integer|min:0|',
            'amount_paid'  => 'nullable|integer|min:0|',
            'due_date' => 'nullable|date|before_or_equal:now',
            'commission_amount'=>'nullable|integer',
            'notes' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:1000',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'فشل التحقق من صحة البيانات',
            'errors' => $validator->errors(),
        ], 422));
    }
}
