<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'kana' => ['nullable', 'string', 'max:255'],
            'tel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:7'],
            'address' => ['nullable', 'string', 'max:100'],
            'birthday' => ['nullable', 'date'],
            'gender' => ['nullable', 'integer', 'between:0,2'],
            'memo' => ['nullable', 'string', 'max:100'],
        ];
    }
}
