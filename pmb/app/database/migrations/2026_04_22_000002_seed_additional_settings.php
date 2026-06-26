<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'key'   => 'brosur',
                'value' => json_encode([
                    ['label' => 'Unduh Brosur PMB BIM',            'url' => 'https://drive.google.com/file/d/1J3bXz8BPUO02D6pHZkEanzCF2Ei1SqTz/view?usp=sharing'],
                    ['label' => 'Unduh Brosur Program Beasiswa BIM', 'url' => 'https://drive.google.com/file/d/12GclvQszmGtO90oRd6DNEW9lswgL7mpf/view'],
                ]),
            ],
            [
                'key'   => 'jalur_masuk',
                'value' => json_encode([
                    [
                        'title' => 'Beasiswa Full',
                        'desc'  => 'Program Beasiswa Full 100% yang diberikan melalui dukungan Hasnur Group dan KIP K sebagai bentuk nyata komitmen dalam mendukung pendidikan generasi muda. Program ini dirancang untuk memberikan kesempatan belajar yang luas bagi mahasiswa berprestasi dan berkomitmen tinggi, khususnya yang memiliki potensi kepemimpinan dan semangat berbisnis.',
                    ],
                    [
                        'title' => 'Beasiswa Parsial',
                        'desc'  => 'Hasnur Group menawarkan Beasiswa Parsial berupa potongan biaya pendidikan hingga 50% bagi mahasiswa yang memenuhi syarat. Program ini bertujuan mendukung pendidikan generasi muda yang memiliki potensi akademik maupun non-akademik, sehingga mereka dapat mengembangkan diri secara optimal selama masa studi.',
                    ],
                    [
                        'title' => 'Jalur Umum',
                        'desc'  => 'Pendaftaran pendidikan strata 1 (S1) secara mandiri yang terbuka untuk semua program studi dan program class yang ada di BIM University tanpa batasan prestasi khusus dengan syarat proses administrasi yang sederhana, serta kesempatan untuk diterima di program studi favorit sesuai pilihan.',
                    ],
                ]),
            ],
            [
                'key'   => 'wa_admin',
                'value' => '628813709234',
            ],
            [
                'key'   => 'catatan_pendaftaran',
                'value' => '',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                ['value' => $row['value'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['brosur', 'jalur_masuk', 'wa_admin', 'catatan_pendaftaran'])->delete();
    }
};
