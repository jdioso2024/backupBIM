<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Dashboard eksekutif untuk Pimpinan.
     *
     * CATATAN: seluruh data di bawah masih DUMMY (statis) untuk keperluan
     * desain tampilan. Nantinya tinggal diganti query ke tabel students.
     */
    public function index()
    {
        $tahunSekarang = 2026;

        // 1) Rekap per fakultas (pendaftar, diterima, registrasi, undur diri)
        $fakultas = [
            [
                'nama'       => 'Fakultas Bisnis & Ekonomi Kreatif',
                'pendaftar'  => 1240,
                'diterima'   => 880,
                'registrasi' => 712,
                'undur_diri' => 64,
            ],
            [
                'nama'       => 'Fakultas Teknologi & Sains Terapan',
                'pendaftar'  => 760,
                'diterima'   => 540,
                'registrasi' => 430,
                'undur_diri' => 38,
            ],
            [
                'nama'       => 'Fakultas Pariwisata & Perhotelan',
                'pendaftar'  => 980,
                'diterima'   => 690,
                'registrasi' => 560,
                'undur_diri' => 47,
            ],
            [
                'nama'       => 'Fakultas Desain & Industri Kreatif',
                'pendaftar'  => 520,
                'diterima'   => 360,
                'registrasi' => 281,
                'undur_diri' => 29,
            ],
        ];

        $total = [
            'pendaftar'  => array_sum(array_column($fakultas, 'pendaftar')),
            'diterima'   => array_sum(array_column($fakultas, 'diterima')),
            'registrasi' => array_sum(array_column($fakultas, 'registrasi')),
            'undur_diri' => array_sum(array_column($fakultas, 'undur_diri')),
        ];

        // 2) Perbandingan 6 tahun terakhir (tahun sekarang + 5 tahun sebelumnya)
        $perbandinganTahun = [
            ['tahun' => 2021, 'pendaftar' => 2100, 'diterima' => 1480, 'registrasi' => 1190, 'undur_diri' => 120],
            ['tahun' => 2022, 'pendaftar' => 2480, 'diterima' => 1700, 'registrasi' => 1360, 'undur_diri' => 138],
            ['tahun' => 2023, 'pendaftar' => 2890, 'diterima' => 1980, 'registrasi' => 1605, 'undur_diri' => 151],
            ['tahun' => 2024, 'pendaftar' => 3120, 'diterima' => 2160, 'registrasi' => 1742, 'undur_diri' => 162],
            ['tahun' => 2025, 'pendaftar' => 3360, 'diterima' => 2310, 'registrasi' => 1880, 'undur_diri' => 170],
            ['tahun' => 2026, 'pendaftar' => $total['pendaftar'], 'diterima' => $total['diterima'], 'registrasi' => $total['registrasi'], 'undur_diri' => $total['undur_diri']],
        ];

        // Perbandingan "sampai tanggal hari ini" — kumulatif pendaftar per bulan,
        // satu garis per tahun. Tahun berjalan berhenti di bulan ke-6 (Juni).
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $kumulatifPendaftar = [
            2024 => [320, 690, 1080, 1520, 1980, 2360, 2620, 2810, 2940, 3030, 3090, 3120],
            2025 => [360, 760, 1190, 1660, 2150, 2540, 2820, 3010, 3160, 3260, 3320, 3360],
            2026 => [410, 880, 1360, 1880, 2420, 2860, null, null, null, null, null, null], // berjalan
        ];

        // 3) Sebaran domisili pendaftar per provinsi (key cocok dgn properti
        //    "Propinsi" pada GeoJSON: huruf kapital).
        $domisili = [
            'BALI'                       => 1820,
            'JAWA TIMUR'                 => 640,
            'NUSATENGGARA BARAT'         => 410,
            'NUSA TENGGARA TIMUR'        => 360,
            'DKI JAKARTA'                => 290,
            'JAWA BARAT'                 => 250,
            'JAWA TENGAH'                => 180,
            'DAERAH ISTIMEWA YOGYAKARTA' => 150,
            'KALIMANTAN SELATAN'         => 130,
            'KALIMANTAN TIMUR'           => 95,
            'SULAWESI SELATAN'           => 88,
            'SUMATERA UTARA'             => 72,
            'PROBANTEN'                  => 64, // Banten
            'SUMATERA SELATAN'           => 41,
            'LAMPUNG'                    => 33,
            'PAPUA'                      => 28,
            'RIAU'                       => 22,
            'SULAWESI UTARA'             => 18,
        ];
        arsort($domisili);
        $domisiliTop = array_slice($domisili, 0, 8, true);

        // 4) Sebaran asal sekolah (top 10)
        $asalSekolah = [
            'SMAN 1 Denpasar'        => 142,
            'SMAN 4 Denpasar'        => 118,
            'SMKN 1 Denpasar'        => 96,
            'SMAN 1 Singaraja'       => 88,
            'SMAN 2 Denpasar'        => 81,
            'SMA Negeri 1 Mataram'   => 74,
            'SMAN 1 Tabanan'         => 69,
            'SMKN 2 Denpasar'        => 63,
            'SMA Negeri 1 Gianyar'   => 58,
            'SMAN 1 Kuta Utara'      => 52,
        ];

        return view('pages.pimpinan.dashboard', compact(
            'tahunSekarang',
            'fakultas',
            'total',
            'perbandinganTahun',
            'bulan',
            'kumulatifPendaftar',
            'domisili',
            'domisiliTop',
            'asalSekolah',
        ));
    }
}
