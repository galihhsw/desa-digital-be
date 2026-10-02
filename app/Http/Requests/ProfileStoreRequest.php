<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class ProfileStoreRequest extends FormRequest
{
   
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required|string',
            'about' => 'required|string',
            'headman' => 'required|string',
            'people' => 'required|integer',
            'agricultural_area' => 'required|numeric',
            'total_area' => 'required|numeric',

            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ];
    }

    public function attributes()
    {
        return [
            'thumbnail' => 'Thumbnail',
            'name' => 'Nama',
            'about' => 'Deskripsi',
            'headman' => 'kepala Desa',
            'people' => 'Jumlah Penduduk',
            'agricultural_area' => 'Luas Wilayah Pertanian',
            'total_area' => 'Luas Wilayah Total',

            'images' => 'Gambar Profil',
            'images.*' => 'Gambar Profil',
        ];
    }

    public function messages()
    {
        return [
            'thumbnail.required' => 'Thumbnail wajib diisi.',
            'thumbnail.image' => 'Thumbnail harus berupa file gambar.',
            'thumbnail.mimes' => 'Thumbnail harus berupa file dengan format: jpeg, png, jpg, gif, svg.',
            'thumbnail.max' => 'Thumbnail tidak boleh lebih dari 2MB.',

            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa string.',

            'about.required' => 'Deskripsi wajib diisi.',
            'about.string' => 'Deskripsi harus berupa string.',

            'headman.required' => 'Kepala Desa wajib diisi.',
            'headman.string' => 'Kepala Desa harus berupa string.',

            'people.required' => 'Jumlah Penduduk wajib diisi.',
            'people.integer' => 'Jumlah Penduduk harus berupa angka.',

            'agricultural_area.required' => 'Luas Wilayah Pertanian wajib diisi.',
            'agricultural_area.numeric' => 'Luas Wilayah Pertanian harus berupa angka.',

            'total_area.required' => 'Luas Wilayah Total wajib diisi.',
            'total_area.numeric' => 'Luas Wilayah Total harus berupa angka.',

            'images.array' => 'Gambar Profil harus berupa array.',
            'images.*.image' => 'Gambar Profil harus berupa file gambar.',
            'images.*.mimes' => 'Gambar Profil harus berupa file dengan format: jpeg, png, jpg, gif, svg.',
            'images.*.max' => 'Gambar Profil tidak boleh lebih dari 2MB.',
        ];
    }
}
