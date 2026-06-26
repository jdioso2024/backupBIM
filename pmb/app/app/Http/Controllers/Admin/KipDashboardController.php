<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use App\Models\Program;
use App\Models\RegisterPeriod;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KipDashboardController extends Controller
{
    public function index(Request $request)
    {
        $activePeriod = RegisterPeriod::active()->first();
        $query = Student::with('user', 'prodi1', 'prodi2', 'studentDocument', 'biodata', 'jalurPendaftaran', 'program')
            ->whereHas('user', function ($query) {
                $query->whereHas('permissions', function ($subQuery) {
                    $subQuery->where('name', 'kip');
                });
            })
            ->where('register_period_id', $activePeriod->id);
        $programs = Program::all();
        // Filter berdasarkan pilihan


        // Filter berdasarkan program studi
        if ($request->has('prodi') && !empty($request->prodi)) {
            if ($request->has('pilihan_prodi') && !empty($request->pilihan_prodi)) {
                $prodi = $request->input('prodi');
                $prodi = Prodi::where('code', $prodi)->first();
                $filterChoice = $request->input('pilihan_prodi');

                if ($filterChoice == 'pertama') {
                    // Filter logika untuk pilihan 1
                    $query->where('prodi1_id', $prodi->id);
                } elseif ($filterChoice == 'kedua') {
                    // Filter logika untuk pilihan 2
                    $query->where('prodi2_id', $prodi);
                }
            }
        }
        if ($request->has('filter_program') && !empty($request->filter_program)) {
            $program = $request->input('filter_program');
            $program = Program::where('id', $program)->first();
            $query->where('program_id', $program->id);
        }

        if ($request->has('student') && !empty($request->student)) {
            $query->where('name', 'like', '%' . $request->student . '%');
        }

        // Menambahkan pagination
        $perPage = $request->get('per_page', 10); // default 10 jika tidak ada per_page di request
        $students = $query->orderByDesc('created_at')->paginate($perPage);

        $prodis = Prodi::all();

        return view('pages.admrektorat.kip.dashboard', compact('students', 'prodis', 'programs'));
    }

    public function show(Student $student)
    {
        $student->load('user', 'prodi1', 'prodi2', 'studentDocument', 'biodata', 'jalurPendaftaran', 'program');
        return view('pages.admrektorat.kip.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load('user', 'prodi1', 'prodi2', 'studentDocument', 'biodata', 'jalurPendaftaran', 'program');
        return view('pages.admrektorat.kip.edit', compact('student'));
    }

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'student' => 'required|integer|exists:students,id',
            'user' => 'required|integer|exists:users,id',
            'slip_transfer' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nilai_rapot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'pas_foto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'follow_ig' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'address' => 'nullable|string|max:255',
            'parent_work' => 'nullable|string|max:255',
            'parent_income' => 'nullable|string|min:0',
            'emergency_contact' => 'nullable|numeric',
            'reason_scholarship' => 'nullable|string',
        ]);

        $student = Student::with('biodata')->where('user_id', $data['user'])->firstOrFail();
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
