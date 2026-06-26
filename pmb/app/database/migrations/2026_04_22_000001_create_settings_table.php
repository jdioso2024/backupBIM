<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'site_logo', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'formulir_description', 'value' => 'BIM (Bali International Management) by ITBM Bali empowered by Hasnur Group dengan bangga mengumumkan pembukaan pendaftaran bagi calon mahasiswa/mahasiswi untuk tahun akademik 2024/2025. Kami menawarkan berbagai program studi yang langsung terkoneksi dengan industri, sehingga memberikan peluang kerja setelah lulus.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alur_pendaftaran', 'value' => json_encode([
                ['text' => 'Mendaftar secara online melalui website PMB BIM'],
                ['text' => 'Peserta akan mendapatkan informasi pembayaran melalui Email.'],
                ['text' => 'Membayar biaya pendaftaran Rp 200.000,-, ke rekening BIM'],
                ['text' => "Mengisi Formulir dan upload dokumen:\n- Slip Transfer Biaya Pendaftaran\n- Kartu Tanda Penduduk\n- Kartu Keluarga\n- Akte/Surat Kenal Lahir\n- Pas Foto (background merah, ukuran max. file size 1 MB)\n- Ijazah SMA/MA/SMK atau Surat Keterangan Lulus\n- Sertifikat prestasi (jika ada) dalam bentuk PDF"],
                ['text' => 'Hasil Seleksi akan diumumkan melalui Email masing-masing yang digunakan saat mendaftar'],
            ]), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
