<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBiodataRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'numeric', 'min_digits:10', 'max_digits:13'],
            'alamat' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'nik' => ['required', 'numeric', 'min_digits:16', 'max_digits:16'],
            'nama_orangtua' => ['required', 'string'],
            'nomor_hp_orangtua' => ['required', 'numeric', 'min_digits:10', 'max_digits:13'],
            'nik_orangtua' => ['required', 'numeric', 'min_digits:16', 'max_digits:16'],
            'hubungan' => ['required', 'string'],
            'hubungan_lainnya' => ['nullable', 'string'],
            'dok_tempat_tinggal' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }
}
