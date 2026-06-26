<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\RegisterPeriod;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentKipExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $activePeriod = RegisterPeriod::active()->first();
        return Student::with(['prodi1', 'prodi2', 'program', 'studentDocument', 'jalurPendaftaran', 'biodata'])
            ->whereHas('user', function ($query) {
                $query->whereHas('permissions', function ($subQuery) {
                    $subQuery->where('name', 'kip');
                });
            })
            ->where('register_period_id', $activePeriod->id)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Nomor Telpon',
            'Jalur Pendaftaran',
            'Prodi 1',
            'Prodi 2',
            'Program',
            'Tanggal Daftar',
            'Asal Sekolah',
            'NISN',
            'Nomor KIP',
            'Nama Orang Tua',
            'Kontak Orang Tua',
            'Penghasilan Orang Tua',
            'Alamat',
            'Pekerjaan Orang Tua',
            'Alasan Layak Mendapatkan Beasiswa',
            'Status',
            'Slip Transfer (Pendaftaran)',
            'Ijazah (Pendaftaran)',
            'Pas Foto (Pendaftaran)',
            'Nilai Rapot (Pendaftaran)',
            'Bukti Follow IG (Pendaftaran)',
            'KTP (Daftar Ulang)',
            'Pernyataan Ortu (Daftar Ulang)',
            'Keterangan Penghasilan (Daftar Ulang)',
            'Pernyataan Diri (Daftar Ulang)',
        ];
    }

    public function map($student): array
    {
        return [
            $student->name,
            $student->phone_number,
            $student->jalurPendaftaran->name ?? 'N/A',
            $student->prodi1->name ?? 'N/A',
            $student->prodi2->name ?? 'N/A',
            $student->program->id == 1 ? 'REG' : ($student->program->id == 2 ? 'INT' : 'EXE'),
            $student->register_at,
            $student->biodata->asal_sekolah,
            $student->biodata->nisn,
            $student->biodata->no_kip ?? 'N/A',
            $student->biodata->nama_orangtua,
            $student->biodata->nomor_hp_orangtua,
            $student->biodata->alamat ?? 'N/A',
            $student->biodata->parent_income ?? '0',
            $student->biodata->parent_work ?? 'N/A',
            $student->biodata->reason_scholarship ?? 'N/A',
            $this->getStatus($student->status),
            $this->getMediaUrl($student->biodata, 'slip_transfer', 'student_document'),
            $this->getMediaUrl($student->biodata, 'ijazah', 'student_document'),
            $this->getMediaUrl($student->biodata, 'pas_foto', 'student_document'),
            $this->getMediaUrl($student->biodata, 'nilai_rapot', 'student_document'),
            $this->getMediaUrl($student->biodata, 'follow_ig', 'student_document'),
            $this->getMediaUrl($student, 'ktp', 'daftarUlang'),
            $this->getMediaUrl($student, 'pernyataan_ortu', 'daftarUlang'),
            $this->getMediaUrl($student, 'keterangan_penghasilan', 'daftarUlang'),
            $this->getMediaUrl($student, 'pernyataan_diri', 'daftarUlang'),

        ];
    }

    protected function getMediaUrl($media, $field, $collection = 'kelengkapanBerkas')
    {
        $dokTemp = $media->getMedia($collection)->firstWhere('name', $field);
        return optional($dokTemp)->getUrl() ?: 'Belum Upload';
    }


    protected function getStatus($status)
    {
        switch ($status) {
            case 0:
                return 'Berkas Belum Lengkap';
            case 1:
                return 'Slip Transfer Sudah dikonfirmasi';
            case 2:
                return 'Diterima Pilihan #1';
            case 3:
                return 'Diterima Pilihan #2';
            case 4:
                return 'Ditolak';
            case 5:
                return 'Lolos Seleksi Berkas';
            default:
                return 'Belum Mengupload Berkas';
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Make heading row bold
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $cellRange = 'A1:Z' . $sheet->getHighestRow(); // All data range

                // Apply border to all cells
                $sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Auto-size columns
                foreach (range('A', 'H') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }

                // Sizing column document
                $columnsDocument = range('I', 'Z');
                foreach ($columnsDocument as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(false);
                    // Set a fixed width that is sufficient to fit "Lihat Dokumen"
                    $sheet->getColumnDimension($columnID)->setWidth(15);
                }
                // Set hyperlinks
                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(FALSE); // Loop through all cells, even if they are empty

                    foreach ($cellIterator as $cell) {
                        $cellValue = $cell->getValue();
                        if (filter_var($cellValue, FILTER_VALIDATE_URL)) {
                            $cell->getHyperlink()->setUrl($cellValue);
                            $cell->getHyperlink()->setTooltip('Klik untuk membuka URL');
                        }
                    }
                }
            },
        ];
    }
}
