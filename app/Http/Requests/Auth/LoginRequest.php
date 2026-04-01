<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
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
            'username' => 'required|string|max:255',
            'password' => 'required|min:8',
            'remember' => 'bool',
        ];
    }

    /**
     * Determine personalized messages for the requests.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'O campo Usuário não pode estar vázio.',
            'username.max' => 'O campo usuário deve ter menos de 255 caracteres.',
            'password.required' => 'O campo da senha não pode estar vázio.',
            'password.min' => 'O campo da senha não pode ter menos de 8 caracteres.',
        ];
    }
}
