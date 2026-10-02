<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class EventParticipantStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => 'required|exists:events,id',
            'head_of_family_id' => 'required|exists:head_of_families,id',
            'quantity' => 'required|integer',
        ];
    }

    public function attributes()
    {
        return [
            'event_id' => 'Event',
            'head_of_family_id' => 'Kepala Keluarga',
            'quantity' => 'Jumlah Peserta',
            'total_price' => 'Total Harga',
        ];
    }

    public function messages()
    {
        return [
            'event_id.required' => 'Event wajib diisi.',
            'event_id.exists' => 'Event tidak valid.',
            'head_of_family_id.required' => 'Kepala Keluarga wajib diisi.',
            'head_of_family_id.exists' => 'Kepala Keluarga tidak valid.',
            'quantity.required' => 'Jumlah Peserta wajib diisi.',
            'quantity.integer' => 'Jumlah Peserta harus berupa angka.',
        ];
    }
}
