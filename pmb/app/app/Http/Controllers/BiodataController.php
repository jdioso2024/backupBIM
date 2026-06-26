<?php

namespace App\Http\Controllers;

use App\Models\Biodata;
use App\Http\Requests\StoreBiodataRequest;
use App\Http\Requests\UpdateBiodataRequest;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BiodataController extends Controller
{
    public function studentRegister(Student $student)
    {

        if ($student->status == 2 || $student->status == 3 || $student->status == 5) {
            $biodata = Biodata::where('student_id', $student->id)->first();
            return view('pages.student.daftar-ulang.student-register', compact('student', 'biodata'));
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }
    public function storeStudentRegister(StoreBiodataRequest $request, Student $student)
    {
        if ($student->status == 2 || $student->status == 3) {
            $data = $request->validated();

            try {
                session([
                    'step1' => [
                        'name' => $data['name'],
                        'nomor_hp' => $data['no_hp'],
                        'alamat' => $data['alamat'],
                        'tanggal_lahir' => $data['tanggal_lahir'],
                        'nik' => $data['nik'],
                        'nama_orangtua' => $data['nama_orangtua'],
                        'nomor_hp_orangtua' => $data['nomor_hp_orangtua'],
                        'nik_orangtua' => $data['nik_orangtua'],
                        'hubungan' => $data['hubungan'],
                        'dok_tempat_tinggal' => $data['hubungan_lainnya'] ?? null,
                    ]
                ]);

                return redirect()->route('student.daftar-ulang.administrasi', $student);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', 'Terjadi kesalahan' . $th->getMessage());
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function studentRegister2(Student $student)
    {
        if ($student->status == 2 || $student->status == 3) {
            $student = Student::with('biodata')->find($student->id);
            return view('pages.student.daftar-ulang.administrasi', compact('student'));
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }
    public function storeStudentRegister2(Request $request, Student $student)
    {
        if ($student->status == 2 || $student->status == 3) {
            // Validasi file
            $data = $request->validate([
                'pas_foto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'pernyataan_diri' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'pernyataan_ortu' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'keterangan_penghasilan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            try {

                $previousData = session('step1', []);

                // Simpan data ke database
                $biodata = Biodata::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'name' => $previousData['name'],
                        'nomor_hp' => $previousData['nomor_hp'],
                        'alamat' => $previousData['alamat'],
                        'tanggal_lahir' => $previousData['tanggal_lahir'],
                        'nik' => $previousData['nik'],
                        'nama_orangtua' => $previousData['nama_orangtua'],
                        'nomor_hp_orangtua' => $previousData['nomor_hp_orangtua'],
                        'nik_orangtua' => $previousData['nik_orangtua'],
                        'hubungan' => $previousData['hubungan'],
                    ]
                );

                // Simpan file ke media collection
                foreach ($data as $key => $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
                        $biodata->media()->where('name', $key)->where('collection_name', 'daftarUlang')->delete();

                        $this->processFile($image, $key, $biodata, 'daftarUlang');
                    }
                }

                // Redirect ke langkah berikutnya
                if (($student->status == 2 || $student->status == 3) && $student->user->hasPermissionTo('kip')) {
                    return redirect()->route('dashboard')->with('successDaftarUlang', 'Daftar Ulang berhasil');
                }
                return redirect()->route('student.daftar-ulang.pembayaran', $student);
            } catch (\Throwable $th) {
                // Tangani kesalahan dan kirim pesan kesalahan
                return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
            }
        } else {
            // Unauthorized jika status tidak valid
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function studentRegister3(Student $student)
    {
        if (($student->status == 2 || $student->status == 3) && $student->user->hasPermissionTo('kip')) {
            return view('dashboard', compact('student'));
        }

        if ($student->status == 2 || $student->status == 3) {
            return view('pages.student.daftar-ulang.payment', compact('student'));
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }
    public function storeStudentRegister3(Request $request, Student $student)
    {
        if ($student->status == 2 || $student->status == 3) {
            $data = $request->validate([
                'bukti_pembayaran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            try {

                // Simpan data ke database
                $biodata = Biodata::where('student_id', $student->id)->first();


                // Simpan file ke storage dan ambil path-nya
                if ($data['bukti_pembayaran'] instanceof \Illuminate\Http\UploadedFile && $data['bukti_pembayaran']->isValid()) {
                    $biodata->media()->where('name', 'bukti_pembayaran')->delete();

                    $this->processFile($data['bukti_pembayaran'], 'bukti_pembayaran', $biodata, 'daftarUlang');
                }

                // Bersihkan session
                session()->forget('step1');

                return redirect()->route('dashboard', $student)->with('successDaftarUlang', 'Daftar Ulang berhasil');
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', 'Terjadi kesalahan ' . $th->getMessage());
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function storeStudentRegisterScholarship(StoreBiodataRequest $request, Student $student)
    {
        if ($student->status == 5 || $student->status == 2 || $student->status == 3) {
            $data = $request->validated();

            DB::beginTransaction();
            try {
                $biodata = Biodata::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'name' => $data['name'],
                        'nomor_hp' => $data['no_hp'],
                        'alamat' => $data['alamat'],
                        'tanggal_lahir' => $data['tanggal_lahir'],
                        'nik' => $data['nik'],
                        'nama_orangtua' => $data['nama_orangtua'],
                        'nomor_hp_orangtua' => $data['nomor_hp_orangtua'],
                        'nik_orangtua' => $data['nik_orangtua'],
                        'hubungan' => $data['hubungan'],
                    ]
                );

                if ($data) {
                    foreach ($data as $key => $image) {
                        if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
                            $biodata->media()->where('name', $key)->where('collection_name', 'daftarUlang')->delete();
                            $this->processFile($image, $key, $biodata, 'daftarUlang');
                        }
                    }
                }

                DB::commit();
                return redirect()->route('student.daftar-ulang.administrasi', $student)->with('success', 'Biodata Berhasil tersimpan!');
            } catch (\Throwable $th) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Terjadi kesalahan' . $th->getMessage());
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized, anda sudah dinyatakan diterima/ditolak',
            ], 401);
        }
    }

    public function studentRegisterPrintCard(Student $student)
    {
        return view('pages.student.daftar-ulang.print-card-test', compact('student'));
    }

    public function downloadStudentCard(Student $student)
    {
        $biodata = Biodata::with('student')->where('student_id', $student->id)->first();

        $pdf = Pdf::loadView('pages.student.daftar-ulang.pdf.index', compact('biodata'));
        return $pdf->download('kartu-daftar-ulang.pdf');
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
