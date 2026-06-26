<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamDataRequest extends FormRequest
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
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'duration' => 'required|integer',
            'url' => 'required|url',
            'code' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Tanggal harus berupa tanggal',
            'time.required' => 'Waktu ujian wajib diisi',
            'time.time' => 'Waktu ujian harus waktu',
            'duration.required' => 'Durasi wajib diisi',
            'duration.integer' => 'Durasi harus berupa angka dan dalam satuan menit',
            'url.required' => 'URL wajib diisi',
            'url.url' => 'URL ujian tidak valide',
            'code.string' => 'Kode Ujian harus berupa string',
        ];
    }
}
