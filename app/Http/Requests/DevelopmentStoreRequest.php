<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class DevelopmentStoreRequest extends FormRequest
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
            'description' => 'required|string',
            'person_in_charge' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'amount' => 'required|integer',
            'status' => 'required|in:ongoing,completed',

        ];
    }

    public function attributes()
    {
        return [
            'thumbnail' => 'Thumbnail',
            'name' => 'Nama',
            'description' => 'Deskripsi',
            'person_in_charge' => 'Penanggung Jawab',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'amount' => 'Jumlah Dana',
            'status' => 'Status',
        ];
    }

    public function messages()
    {
        return [
            'thumbnail.required' => 'Thumbnail wajib diisi.',
            'thumbnail.image' => 'Thumbnail harus berupa file gambar.',
            'thumbnail.mimes' => 'Thumbnail harus berupa file dengan format: jpeg, png, jpg, gif, svg.',
            'thumbnail.max' => 'Ukuran thumbnail tidak boleh lebih dari 2MB.',
            'name.required' => 'Nama wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'person_in_charge.required' => 'Penanggung Jawab wajib diisi.',
            'start_date.required' => 'Tanggal Mulai wajib diisi.',
            'start_date.date' => 'Tanggal Mulai harus berupa tanggal yang valid.',
            'end_date.required' => 'Tanggal Selesai wajib diisi.',
            'end_date.date' => 'Tanggal Selesai harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal Selesai harus sama atau setelah Tanggal Mulai.',
            'amount.required' => 'Jumlah Dana wajib diisi.',
            'amount.integer' => 'Jumlah Dana harus berupa angka bulat.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => "Status harus salah satu dari: ongoing, completed.",
        ];
    }
}
