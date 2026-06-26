<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Services\OdsApiService;

class MonitorController extends Controller
{
    public function __construct(private OdsApiService $ods) {}

    // ── helpers ──────────────────────────────────────────────────────────────

    private function totals(array $prodis): array
    {
        return [
            'kuota'      => array_sum(array_column($prodis, 'kuota')),
            'pendaftar'  => array_sum(array_column($prodis, 'pendaftar')),
            'diterima'   => array_sum(array_column($prodis, 'diterima')),
            'registrasi' => array_sum(array_column($prodis, 'registrasi')),
        ];
    }

    private function tahunSekarang(): int
    {
        return (int) now()->format('Y');
    }

    private static function bulan(): array
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    }

    // ── endpoints ─────────────────────────────────────────────────────────────

    /** 1) Data Rekap (S1 / D3 / D4) */
    public function rekap()
    {
        $data   = $this->ods->rekap();
        $prodis = $data['prodis'] ?? [];
        $total  = $this->totals($prodis);
        $tahunSekarang = $this->tahunSekarang();

        return view('pages.pimpinan.monitor.rekap.index', compact('tahunSekarang', 'prodis', 'total'));
    }

    /** 2) Data Rekap Pasca Sarjana (S2 / S3) */
    public function rekapPasca()
    {
        $data   = $this->ods->rekapPasca();
        $prodis = $data['prodis'] ?? [];
        $total  = $this->totals($prodis);
        $tahunSekarang = $this->tahunSekarang();

        return view('pages.pimpinan.monitor.rekap-pasca.index', compact('tahunSekarang', 'prodis', 'total'));
    }

    /** 3) Daftar Program Studi */
    public function programStudi()
    {
        $data = $this->ods->programStudi();
        $s1   = $data['s1'] ?? [];
        $s2   = $data['s2'] ?? [];

        return view('pages.pimpinan.monitor.program-studi.index', compact('s1', 's2'));
    }

    /** 4) Laporan Registrasi */
    public function laporanRegistrasi()
    {
        $data           = $this->ods->laporanRegistrasi();
        $tahunSekarang  = $data['tahun_sekarang'] ?? $this->tahunSekarang();
        $bulan          = self::bulan();
        $dataRegistrasi = $data['data_registrasi'] ?? [];
        $jalur          = $data['jalur'] ?? [];
        $totalReg       = array_sum(array_column($jalur, 'jumlah'));

        return view('pages.pimpinan.monitor.laporan-registrasi.index',
            compact('tahunSekarang', 'bulan', 'dataRegistrasi', 'jalur', 'totalReg'));
    }

    /** 5) Data Detail PMB */
    public function dataDetail()
    {
        $data          = $this->ods->dataDetail();
        $tahunSekarang = $data['tahun_sekarang'] ?? $this->tahunSekarang();
        $pendaftar     = $data['pendaftar'] ?? [];

        return view('pages.pimpinan.monitor.data-detail.index', compact('tahunSekarang', 'pendaftar'));
    }

    /** 6) Perbandingan Per Tahun */
    public function perbandinganTahun()
    {
        $data         = $this->ods->perbandinganTahun();
        $perbandingan = $data['perbandingan'] ?? [];
        $bulan        = self::bulan();
        $kumulatif    = $data['kumulatif'] ?? [];

        return view('pages.pimpinan.monitor.perbandingan-tahun.index',
            compact('perbandingan', 'bulan', 'kumulatif'));
    }

    /** 7) Sebaran Domisili Pendaftar */
    public function sebaranDomisili()
    {
        $data     = $this->ods->sebaranDomisili();
        $domisili = $data['domisili'] ?? [];
        arsort($domisili);
        $total = $data['total'] ?? array_sum($domisili);

        return view('pages.pimpinan.monitor.sebaran-domisili.index', compact('domisili', 'total'));
    }

    /** 8) Sebaran Asal Sekolah */
    public function sebaranSekolah()
    {
        $data    = $this->ods->sebaranSekolah();
        $sekolah = $data['sekolah'] ?? [];
        arsort($sekolah);
        $total = $data['total'] ?? array_sum($sekolah);

        return view('pages.pimpinan.monitor.sebaran-sekolah.index', compact('sekolah', 'total'));
    }
}
