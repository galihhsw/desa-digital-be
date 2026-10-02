<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SocialAssistanceStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'thumbnail' => 'required|image|mimes:png,jpg|max:2048',
            'name' => 'required|string|max:255',
            'category' => 'required|in:staple,cash,subsidized fuel,health',
            'amount' => 'required',
            'provider' => 'required|string',
            'description' => 'required',
            'is_available' => 'required|boolean',
            

        ];
    }

    public function attributes()
    {
        return [
            'thumbnail' => 'Thumbnail',
            'name' => 'Nama',
            'category' => 'Kategori',
            'amount' => 'Jumlah Bantuan',
            'provider' => 'Penyedia/Instansi',
            'description' => 'Deskripsi',
            'is_available' => 'Ketersediaan',

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
}
