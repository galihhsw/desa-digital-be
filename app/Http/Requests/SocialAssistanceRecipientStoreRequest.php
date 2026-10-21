<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SocialAssistanceRecipientStoreRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'social_assistance_id' => 'required|exists:social_assistances,id',
            'head_of_family_id' => 'required|exists:head_of_families,id',
            'amount' => 'required|integer',
            'reason' => 'required|string',
            'bank' => 'required|string|in:bri,bni,mandiri,bca',
            'account_number' => 'required',
            'proof' => 'nullable|image',
            'status' => 'nullable|string|in:pending,approved,rejected'
        ];
    }

    public function attributes()
    {
        return [
            'social_assistance_id' => 'Bantuan Sosial',
            'head_of_family_id' => 'Kepala Keluarga',
            'amount' => 'Nominal',
            'reason' => 'Alasan',
            'bank' => 'Bank',
            'account_number' => 'Rekening Bank',
            'proof' => 'Bukti/Instansi',
            'status' => 'Status',

        ];
    }

    public function messages()
    {
        return[
            'required' => ':attribute harus diisi ',
            'string' => ':attribute harus berupa string',
            'max' => ':attribute maksimal :max karakter',
            'unique' => ':attribute sudah ada',
            'image' => ':attribute harus berupa gambar',
            'email' => ':attribute harus berupa email',
            'min' => ':attribute minimal :min karakter',
            'exists' => ':attribute tidak ditemukan',
            'integer' => ':attribute harus berupa angka',
            'array' => ':attribute harus berupa array',
            'mimes' => ':attribute harus berupa gambar',
            'max:2048' => ':attribute maksimal 2048 KB',
            'unique:users' => ':attribute sudah ada',
            'in' => ':attribute harus berupa salah satu dari :values'
        ];
    }

    public function prepareForValidation()
    {
        $user = Auth::user();

        if ($user->hasRole('head-of-family')) {
            $this->merge(['head_of_family_id' => $user->headOfFamily->id]);
        }
    }

}
