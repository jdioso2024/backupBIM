<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KipRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'gender' => ['required', 'string', 'in:Laki-laki,Perempuan'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'email' => ['required', 'email', 'unique:users,email'],
            'asal_sekolah' => ['required', 'string'],
            'nisn' => ['required', 'string', 'min:10'],
            'nomor_hp' => ['required', 'numeric', 'min_digits:10', 'max_digits:13'],
            'nama_orangtua' => ['required', 'string'],
            'nomor_hp_orangtua' => ['required', 'numeric', 'min_digits:10', 'max_digits:13'],
            'no_kip' => ['nullable', 'string'],
            'prodi1_id' => ['required', 'string'],
            'prodi2_id' => ['required', 'string'],
        ];
    }
}
