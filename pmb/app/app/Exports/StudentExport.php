<?php

namespace App\Exports;

use App\Models\RegisterPeriod;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $activePeriod = RegisterPeriod::active()->first();
        return Student::with(['prodi1', 'prodi2', 'program', 'studentDocument', 'jalurPendaftaran'])
            ->where('register_period_id', $activePeriod->id)
            ->whereHas('jalurPendaftaran', function ($query) {
                $query->where('name', '!=', 'KIP Kuliah');
            })
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Phone Number',
            'Jalur Pendaftaran',
            'Prodi 1',
            'Prodi 2',
            'Program',
            'Tanggal Daftar',
            'Status',
            'Slip Transfer',
            'Kartu Keluarga',
            'Akta Lahir',
            'KTP',
            'Ijazah',
            'Pas Foto',
            'Nilai Rapot',
            'CV',
            'Surat Rekomendasi',
            'Esai',
            'Rapot Semester Akhir',
            'Dokumentasi Tempat Tinggal'
        ];
        // return [
        //     'Nama Lengkap',
        //     'Phone Number',
        //     'Jalur Pendaftaran',
        //     'Prodi 1',
        //     'Prodi 2',
        //     'Program',
        //     'Tanggal Daftar',
        //     'Status',
        //     'Slip Transfer',
        //     'Kartu Keluarga',
        //     'Akta Lahir',
        //     'KTP',
        //     'Ijazah',
        //     'Pas Foto',
        //     'Nilai Rapot',
        //     'CV',
        //     'Surat Rekomendasi',
        //     'Esai',
        //     'Prestasi',
        //     // 'KTP (Media)',
        //     // 'Ijazah (Media)',
        //     // 'Pernyataan Ortu',
        //     // 'Keterangan Penghasilan',
        //     // 'Pas Foto (Media)',
        //     // 'Pernyataan Diri',
        //     // 'Bukti Pembayaran',
        // ];
    }

    public function map($student): array
    {
        $commonData = [
            $student->name,
            $student->phone_number,
            $student->jalurPendaftaran->name ?? 'N/A',
            $student->prodi1->name ?? 'N/A',
            $student->prodi2->name ?? 'N/A',
            $student->program->id == 1 ? 'REG' : ($student->program->id == 2 ? 'INT' : 'EXE'),
            $student->register_at,
            $this->getStatus($student->status),
        ];

        // Jika jalur pendaftaran adalah KIP Kuliah
        // if ($student->jalurPendaftaran && $student->jalurPendaftaran->name === 'KIP Kuliah') {
        //     $documentData = [
        //         $student->biodata->alamat ?? '-',
        //         $student->biodata->parent_work ?? '-',
        //         $student->biodata->parent_income ?? '-',
        //         $student->biodata->emergency_contact ?? '-',
        //         $student->biodata->reason_scholarship ?? '-',
        //         $this->getMediaUrl($student->biodata, 'slip_transfer', 'student_document'),
        //         $this->getMediaUrl($student->biodata, 'ijazah', 'student_document'),
        //         $this->getMediaUrl($student->media, 'nilai_rapot', 'student_document'),
        //         $this->getMediaUrl($student->media, 'pas_foto', 'student_document'),
        //         $this->getMediaUrl($student->media, 'follow_ig', 'student_document'),
        //     ];
        // } 
        $documentData = [
            $this->getDocumentUrl($student->studentDocument, 'slip_transfer'),
            $this->getDocumentUrl($student->studentDocument, 'kartu_keluarga'),
            $this->getDocumentUrl($student->studentDocument, 'akta_lahir'),
            $this->getDocumentUrl($student->studentDocument, 'ktp'),
            $this->getDocumentUrl($student->studentDocument, 'ijazah'),
            $this->getDocumentUrl($student->studentDocument, 'pas_foto'),
            $this->getDocumentUrl($student->studentDocument, 'nilai_rapot'),
            $this->getDocumentUrl($student->studentDocument, 'cv'),
            $this->getDocumentUrl($student->studentDocument, 'surat_rekomendasi'),
            $this->getDocumentUrl($student->studentDocument, 'esai'),
            // $this->getDocumentUrl($student->studentDocument, 'prestasi'),
            $this->getMediaUrl($student, 'rapot_sems_akhir'),
            $this->getMediaUrl($student, 'dok_tempat_tinggal')
        ];

        return array_merge($commonData, $documentData);
    }

    protected function getDocumentUrl($document, $field)
    {
        return optional($document)->$field ? url('storage/' . optional($document)->$field) : 'Belum Upload';
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
