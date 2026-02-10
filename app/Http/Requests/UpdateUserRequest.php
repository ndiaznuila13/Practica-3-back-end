<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     */
    public function rules(): array
    {
        // Si es PUT, todos los campos son requeridos
        // Si es PATCH, los campos son opcionales (sometimes)
        $requiredRule = $this->isMethod('put') ? 'required' : 'sometimes';

        return [
            'name' => [$requiredRule, 'string', 'max:255'],
            'lastname' => [$requiredRule, 'string', 'max:255'],
            'username' => [$requiredRule, 'string', 'max:255', Rule::unique('users')->ignore($this->user)],
            'email' => [$requiredRule, 'email', Rule::unique('users')->ignore($this->user)],
            'hiring_date' => ['sometimes', 'date'],
            'dui' => [$requiredRule, 'string', 'regex:/^\d{8}-\d$/', Rule::unique('users')->ignore($this->user)],
            'phone_number' => ['nullable', 'string', 'regex:/^[0-9\-\+\s\(\)]+$/'],
            'birth_date' => [$requiredRule, 'date', 'before:today'],
        ];
    }
}
