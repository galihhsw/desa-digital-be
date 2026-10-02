<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DevelopmentApplicantUpdateRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'development_id' => 'required|exists:developments,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,approved,rejected',
        ];
    }

    public function attributes()
    {
        return [
            'development_id' => 'Pengembangan',
            'user_id' => 'Pengguna',
            'status' => 'Status',
        ];
    }
    
    public function messages()
    {
        return [
            'development_id.required' => 'Pengembangan wajib diisi.',
            'development_id.exists' => 'Pengembangan tidak valid.',
            'user_id.required' => 'Pengguna wajib diisi.',
            'user_id.exists' => 'Pengguna tidak valid.',
            'status.in' => 'Status harus berupa salah satu dari: pending, approved, rejected.',
        ];
    }
}
