<?php

namespace App\Http\Controllers;

use App\Exports\StudentKipExport;
use App\Http\Requests\KipRequest;
use App\Mail\StudentAccountMail;
use App\Models\Biodata;
use App\Models\JalurPendaftaran;
use App\Models\Prodi;
use App\Models\Program;
use App\Models\RegisterPeriod;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class KipController extends Controller
{
    public function index()
    {
        $prodis = Prodi::whereIn('code', ['BD', 'KW', 'TP', 'HK', 'MJ'])->get();
        $programs = Program::all();
        $jalurs = JalurPendaftaran::all();
        return view('kip-register', compact('prodis', 'programs', 'jalurs'));
    }

    public function store(KipRequest $request)
    {
        $data = $request->validated();

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
                $user->givePermissionTo('kip');

                $data['user_id'] = $user->id;
            }

            // Get prodi ids from codes
            $prodi1 = Prodi::where('code', $data['prodi1_id'])->first();
            $prodi2 = Prodi::where('code', $data['prodi2_id'])->first();

            if (!$prodi1 || !$prodi2) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Prodi tidak ditemukan.');
            }
            $activePeriod = RegisterPeriod::active()->first();
            $jalurPendaftaran = JalurPendaftaran::where('name', 'KIP Kuliah')->first();
            // Create new student
            $student = Student::create([
                'user_id' => $data['user_id'],
                'name' => $data['name'],
                'phone_number' => $data['nomor_hp'],
                'referensi' => 'KIP',
                'prodi1_id' => $prodi1->id,
                'prodi2_id' => $prodi2->id,
                'program_id' => 1,
                'register_at' => $data['register_at'],
                'status' => $data['status'],
                'jalur_pendaftaran_id' => $jalurPendaftaran->id,
                'register_period_id' => $activePeriod->id,
            ]);

            $biodata = Biodata::create([
                'student_id' => $student->id,
                'name' => $student->name,
                'nomor_hp' => $student->phone_number,
                'alamat' => '',
                'tanggal_lahir' => $data['tanggal_lahir'],
                'nik' => '',
                'nama_orangtua' => $data['nama_orangtua'],
                'nomor_hp_orangtua' => $data['nomor_hp_orangtua'],
                'nik_orangtua' => '',
                'hubungan' => '',
                'tempat_lahir' => $data['tempat_lahir'],
                'jenis_kelamin' => $data['gender'],
                'asal_sekolah' => $data['asal_sekolah'],
                'nisn' => $data['nisn'],
                'no_kip' => $data['no_kip'],
            ]);

            // Send email notification
            $this->sendEmail($student);

            DB::commit();
            return redirect()->back()->with('success', 'Pendaftaran Berhasil! Silahkan cek email Anda untuk login akun');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nilai_rapot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'pas_foto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'follow_ig' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'address' => 'required|string|max:255',
            'parent_work' => 'nullable|string|max:255',
            'parent_income' => 'nullable|string',
            'emergency_contact' => 'nullable|numeric',
            'reason_scholarship' => 'nullable|string',
            'slip_transfer' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $student = Student::with('biodata')->where('user_id', auth()->user()->id)->firstOrFail();
        $biodata = $student->biodata;

        if (!$biodata) {
            return redirect()->back()->with('error', 'Data biodata tidak ditemukan. Silakan lengkapi biodata terlebih dahulu.');
        }

        try {
            // Simpan alamat, pekerjaan orang tua, penghasilan, dan lainnya
            $biodata->update([
                'alamat' => $data['address'],
                'parent_work' => $data['parent_work'],
                'parent_income' => $data['parent_income'],
                'emergency_contact' => $data['emergency_contact'],
                'reason_scholarship' => $data['reason_scholarship'],
            ]);

            foreach ($data as $key => $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
                    $biodata->media()->where('name', $key)->where('collection_name', 'student_document')->delete();

                    $this->processFile($image, $key, $biodata, 'student_document');
                }
            }

            // Simpan file jika ada
            // if ($request->hasFile('ijazah')) {
            //     $biodata->addMedia($request->file('ijazah'))->usingName('ijazah')->toMediaCollection('student_document');
            // }

            // if ($request->hasFile('nilai_rapot')) {
            //     $biodata->addMedia($request->file('nilai_rapot'))->usingName('nilai_rapot')->toMediaCollection('student_document');
            // }

            // if ($request->hasFile('pas_foto')) {
            //     $biodata->addMedia($request->file('pas_foto'))->usingName('pas_foto')->toMediaCollection('student_document');
            // }

            return redirect()->back()->with('success', 'Dokumen berhasil disimpan!');
        } catch (\Throwable $th) {
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

    public function exportStudent()
    {
        return Excel::download(new StudentKipExport, 'data_mahasiswa_kip.xlsx');
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
