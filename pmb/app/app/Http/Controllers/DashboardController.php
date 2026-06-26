<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())
            ->with('user', 'prodi1', 'prodi2', 'studentDocument', 'biodata')
            ->first();

        // Check if student data exists
        if (!$student) {
            return view('errors.no-student-data');
        }

        $mediaCount = ($student && $student->media)
            ? $student->media->where('collection_name', 'kelengkapanBerkas')->count()
            : 0;

        $document = $student->studentDocument ?? null;
        $fields = [
            'slip_transfer',
            'ktp',
            'kartu_keluarga',
            'akta_lahir',
            'ijazah',
            'prestasi',
            'pas_foto',
            'nilai_rapot',
            'cv',
            'surat_rekomendasi',
            'esai',
        ];

        $anotherMediaCount = optional($student->studentDocument)
            ? collect($fields)->filter(function ($field) use ($student) {
                return !is_null(optional($student->studentDocument)->$field);
            })->count()
            : 0;

        $anotherMediaCount2 = ($student && $student->biodata && $student->biodata->media)
            ? $student->biodata->media->where('collection_name', 'student_document')->count()
            : 0;

        $mediaCount += $anotherMediaCount;
        $mediaCount += $anotherMediaCount2;

        return view('dashboard', compact('student', 'mediaCount'));
    }

    public function storeDocument(Request $request)
    {
        // Validasi
        $data = $request->validate([
            'slip_transfer' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'kartu_keluarga' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'akta_lahir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'prestasi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pas_foto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nilai_rapot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cv' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'surat_rekomendasi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'esai' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'student' => 'nullable|integer|exists:students,id',
            'user' => 'nullable|integer|exists:users,id',
        ]);

        $data2 = $request->validate([
            'rapot_sems_akhir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dok_tempat_tinggal' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Simpan setiap dokumen ke dalam storage
            foreach ($data as $key => $value) {
                if ($request->hasFile($key)) {
                    $data[$key] = $request->file($key)->store(
                        "assets/{$key}",
                        'public'
                    );
                }
            }

            if (auth()->user()->hasRole('student')) {
                $student = Student::where('user_id', auth()->id())->firstOrFail();
            } else if (auth()->user()->hasRole('admrektorat')) {
                $student = Student::where('id', $data['student'])
                    ->where('user_id', $data['user'])
                    ->firstOrFail();
            }
            $student->studentDocument()->updateOrCreate(
                [],
                $data
            );

            if ($data2) {
                foreach ($data2 as $key => $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
                        $student->media()->where('name', $key)->where('collection_name', 'kelengkapanBerkas')->delete();
                        $this->processFile($image, $key, $student, 'kelengkapanBerkas');
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Dokumen berhasil diunggah');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat mengunggah dokumen' . $th->getMessage());
        }
    }

    protected function processFile($file, $fileName, $model, $mediaCollectionName)
    {
        try {
            // Buat nama file yang unik menggunakan UUID
            $hashName = Hash::make($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();

            // Tambahkan media baru ke koleksi
            $media = $model->addMedia($file)
                ->usingName($fileName)
                ->usingFileName($hashName)
                ->toMediaCollection($mediaCollectionName);


            return $media;
        } catch (\Throwable $th) {
            return abort(404, $th->getMessage());
        }
    }
}
