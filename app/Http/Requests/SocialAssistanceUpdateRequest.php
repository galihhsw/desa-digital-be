<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SocialAssistanceUpdateRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

     public function rules(): array
    {
        return [
            'thumbnail' => 'nullable|image|mimes:png,jpg|max:2048',
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
}
