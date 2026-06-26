<?php

namespace App\Http\Controllers;

use App\Exports\StudentExport;
use App\Models\Setting;
use App\Models\Student;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Mail\StudentAcceptedMail;
use App\Mail\StudentAccountCreated;
use App\Mail\StudentAccountMail;
use App\Mail\StudentRejectedRaportMail;
use App\Mail\StudentRejectedScholarshipMail;
use App\Mail\StudentScholarshipDocumentAccepted;
use App\Models\Biodata;
use App\Models\JalurPendaftaran;
use App\Models\Prodi;
use App\Models\Program;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\RegisterPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prodis = Prodi::whereIn('code', ['BD', 'KW', 'TP', 'HK', 'MJ'])->get();
        $programs = Program::all();
        $jalurs = JalurPendaftaran::all();
        $formulirDescription  = Setting::get('formulir_description', '');
        $alurPendaftaran      = json_decode(Setting::get('alur_pendaftaran', '[]'), true) ?? [];
        $siteLogo             = Setting::get('site_logo');
        $brosur               = json_decode(Setting::get('brosur', '[]'), true) ?? [];
        $jalurMasuk           = json_decode(Setting::get('jalur_masuk', '[]'), true) ?? [];
        $waAdmin              = Setting::get('wa_admin', '628813709234');
        $catatanPendaftaran   = Setting::get('catatan_pendaftaran', '');
        $tampilkanForm        = Setting::get('tampilkan_form', '1') === '1';
        return view('welcome', compact('prodis', 'programs', 'jalurs', 'formulirDescription', 'alurPendaftaran', 'siteLogo', 'brosur', 'jalurMasuk', 'waAdmin', 'catatanPendaftaran', 'tampilkanForm'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        if ($data['promo_code']) {
            $promo = PromoCode::where('code', $data['promo_code'])
                ->where('is_active', true)
                ->where('start_date', '<=', today())
                ->where('end_date', '>=', today())
                ->first();

            if (!$promo) {
                return redirect()->back()->with('error', 'Kode promo tidak valid atau sudah kedaluwarsa.');
            }
        }
        if ((int)$data['program'] !== 1) {
            if ((int)$data['jalur'] !== 5) {
                return redirect()->back()->with('error', 'Jalur pendaftaran tidak sesuai dengan program yang dipilih.');
            }
        }

        if ($data['prodi1_id'] === $data['prodi2_id']) {
            return redirect()->back()->with('error', 'Pilihan Prodi 1 dan Prodi 2 tidak boleh sama.');
        }

        DB::beginTransaction();
        try {
            $data['register_at'] = now();
            $data['status'] = 0;

            // Check if email already registered
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                return redirect()->back()->with('error', 'Email sudah terdaftar!');
            } else {
                // Create new user
                // Generate random 8-digit numeric password
                $randomPassword = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($randomPassword),
                    'original_password' => $randomPassword,
                ]);
                $user->assignRole('student');
                if ($data['jalur'] == 1) {
                    $user->givePermissionTo('beasiswa-access');
                } else if ($data['jalur'] == 2) {
                    $user->givePermissionTo('beasiswa-access');
                } else if ($data['jalur'] == 5) {
                    $user->givePermissionTo('reguler-access');
                }

                $data['user_id'] = $user->id;
            }

            if ($data['program'] == 2) {
                // Validate prodi codes for program 2 or 3
                if (!in_array($data['prodi1_id'], ['BD', 'KW']) || !in_array($data['prodi2_id'], ['BD', 'KW'])) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Prodi yang dipilih tidak sesuai dengan program yang dipilih.');
                }
            }

            // Get prodi ids from codes
            $prodi1 = Prodi::where('code', $data['prodi1_id'])->first();
            $prodi2 = Prodi::where('code', $data['prodi2_id'])->first();

            if (!$prodi1 || !$prodi2) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Prodi tidak ditemukan.');
            }
            $activePeriod = RegisterPeriod::active()->first();
            // Create new student
            $student = Student::create([
                'user_id' => $data['user_id'],
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'referensi' => $data['referensi'],
                'prodi1_id' => $prodi1->id,
                'prodi2_id' => $prodi2->id,
                'program_id' => $data['program'],
                'register_at' => $data['register_at'],
                'status' => $data['status'],
                'jalur_pendaftaran_id' => $data['jalur'],
                'register_period_id' => $activePeriod->id,
            ]);

            $biodata = Biodata::create([
                'student_id' => $student->id,
                'nama_orangtua' => $data['parent_name'],
                'nomor_hp_orangtua' => $data['parent_phone'],
                'name' => $student->name,
                'nomor_hp' => $student->phone_number,
                'alamat' => '',
                'tanggal_lahir' => now(),
                'nik' => '',
                'nik_orangtua' => '',
                'hubungan' => '',
            ]);

            if ($data['promo_code']) {
                PromoCodeUsage::create([
                    'promo_code_id' => $promo->id,
                    'user_id' => $data['user_id'],
                    'used_at' => now(),
                ]);

                $promo->update([
                    'usage_count' => $promo->usage_count + 1,
                ]);
            }

            // Send email notification
            $this->sendEmail($student);

            DB::commit();
            return redirect()->back()->with('success', 'Pendaftaran Berhasil! Silahkan cek email Anda untuk login akun');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->load('user', 'prodi1', 'prodi2', 'studentDocument', 'biodata', 'jalurPendaftaran', 'program');
        return view('pages.admrektorat.student.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $student->load('user', 'prodi1', 'prodi2', 'studentDocument', 'biodata', 'jalurPendaftaran', 'program');
        return view('pages.admrektorat.student.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Student $student)
    {
        DB::beginTransaction();
        try {
            $student->update(['status' => 1]);

            DB::commit();
            return redirect()->back()->with('success', 'Data berhasil diupdate');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
    public function changeStatus(Student $student, Request $request)
    {
        $student = $student->load('user', 'program', 'jalurPendaftaran');
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'status' => 'required|string|in:diterima1,diterima2,ditolak,berkas',
        ]);
        if ($student->id == $data['student_id']) {
            DB::beginTransaction();
            try {
                if ($data['status'] != 'ditolak') {
                    if ($data['status'] == 'berkas') {
                        $student->update(['status' => 5]);
                        Mail::to($student->user->email)->send(new StudentScholarshipDocumentAccepted($student));
                    } else {
                        $student->update(['status' => $data['status'] == 'diterima1' ? 2 : 3]);

                        Mail::to($student->user->email)->send(new StudentAcceptedMail($student, $student->prodi));
                    }
                } else {
                    if ($student->jalur_pendaftaran_id == 1) {
                        // beasiswa
                        $student->update(['status' => 4]);

                        Mail::to($student->user->email)->send(new StudentRejectedScholarshipMail($student));
                    } else {
                        // prestasi/rapot
                        $student->update(['status' => 4]);

                        Mail::to($student->user->email)->send(new StudentRejectedRaportMail($student));
                    }
                }

                DB::commit();
                return redirect()->back()->with('success', 'Status calon mahasiswa telah diperbarui');
            } catch (\Throwable $th) {
                DB::rollBack();
                return redirect()->back()->with('error', $th->getMessage());
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No student found',
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        DB::beginTransaction();
        try {
            $student->user->delete();
            $student->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function sendEmail(Student $student)
    {
        $student->load('user');

        try {
            // $student->notify(new StudentAccountCreated($student));
            Mail::to($student->user->email)->send(new StudentAccountMail($student));
            session()->flash('success', 'Email berhasil dikirim');
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
        }
    }

    public function daftarUlang()
    {
        return view('pages.student.daftar-ulang.index');
    }

    public function exportStudent()
    {
        return Excel::download(new StudentExport, 'data_mahasiswa.xlsx');
    }

    public function reblastEmail()
    {
        $students = Student::with('user')
            ->where('status', 0)
            ->where('register_period_id', RegisterPeriod::active()->first()->id)
            ->get();
        try {
            foreach ($students as $student) {
                Mail::to($student->user->email)->send(new StudentAccountMail($student));
            }
            return redirect()->back()->with('success', 'Email berhasil dikirim ulang');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
    public function reblastEmailStudent(Student $student)
    {
        try {
            Mail::to($student->user->email)->send(new StudentAccountMail($student));
            return redirect()->back()->with('success', 'Email berhasil dikirim ulang');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
    public function reblastWaStudent(Student $student)
    {
        try {
            $phone_number = ltrim($student->phone_number, '0'); // Remove leading 0
            $phone_number = '62' . $phone_number; // Add country code

            $message = urlencode("
[PENDAFTARAN BIM (Bali International Management) by ITBM Bali empowered by Hasnur Group]

Berikut detail akun login anda
Nama Lengkap: {$student->user->name}
Email: {$student->user->email}
Password: {$student->user->original_password}
-----------------------------------------------

Silahkan lakukan transfer biaya registrasi sesuai informasi dibawah

Bank: Bank Mandiri
Atas Nama: BIM INOVASI MANDIRI
Nomor Rekening: 1390030116614
Jumlah: Rp. 200.000
Berita Transfer: BIM00{$student->user->id}
-----------------------------------------------

Harap cantumkan berita transfer untuk memudahkan proses registrasi

Silahkan transfer biaya pendaftaran dan upload bukti transfer serta berkas kelengkapan melalui:
➡️https://pmb.bim.ac.id/
");
            return redirect()->away("https://api.whatsapp.com/send?phone={$phone_number}&text={$message}");
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function showDocument(Student $student)
    {
        $student->load('user', 'prodi1', 'prodi2', 'jalurPendaftaran', 'program');
        $data = Biodata::where('student_id', $student->id)->first();

        // Mengambil media yang terkait dengan Biodata menggunakan MediaLibrary
        $mediaItems = $data ? $data->getMedia('daftarUlang') : collect();

        return view('pages.admrektorat.student.showDocument', compact('data', 'mediaItems', 'student'));
    }

    public function validatePromo(Request $request)
    {
        $data = $request->validate([
            'promo_code' => 'required|string',
        ]);

        $promo = PromoCode::where('code', $data['promo_code'])
            ->where('is_active', true)
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();

        if ($promo) {
            // $response = [
            //     'description' => $promo->description,
            //     'type' => $promo->type,
            //     'value' => $promo->value,
            // ];
            return response()->json([
                'status' => 'valid',
                'message' => 'Kode promo valid. ' . $promo->description,
                // 'data' => $response,
            ]);
        } else {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Kode promo tidak valid atau sudah kedaluwarsa.',
            ], 400);
        }
    }
}
