<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use App\Models\Program;
use App\Models\RegisterPeriod;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activePeriod = RegisterPeriod::active()->first();
        $query = Student::with('user', 'prodi1', 'prodi2', 'studentDocument', 'jalurPendaftaran', 'program')
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('permissions', function ($subQuery) {
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

        return view('pages.admrektorat.dashboard', compact('students', 'prodis', 'programs'));
    }
}
