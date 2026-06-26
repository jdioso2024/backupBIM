<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'numeric', 'min_digits:10', 'max_digits:13'],
            'referensi' => ['required', 'string', 'max:255'],
            'prodi1_id' => ['required', 'string'],
            'prodi2_id' => ['required', 'string'],
            'program' => ['required', 'integer', 'exists:programs,id'],
            'jalur' => ['required', 'integer', 'exists:jalur_pendaftarans,id'],
            'promo_code' => ['nullable', 'string', 'exists:promo_codes,code'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['required', 'numeric', 'min_digits:10', 'max_digits:13'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email tidak valid',
            'phone_number.required' => 'Nomor telepon harus diisi',
            'referensi.required' => 'Referensi harus diisi',
            'prodi1_id.required' => 'Prodi 1 harus diisi',
            'prodi2_id.required' => 'Prodi 2 harus diisi',
            'program.required' => 'Harap pilih Program Pilihan',
            'jalur.required' => 'Harap pilih Jalur Pendaftaran',
        ];
    }
}
