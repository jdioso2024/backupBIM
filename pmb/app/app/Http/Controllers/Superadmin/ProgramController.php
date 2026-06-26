<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::latest()->get();
        return view('pages.superadmin.program.index', compact('programs'));
    }


    public function store(StoreProgramRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            Program::create($data);

            DB::commit();
            return redirect()->back()->with('success', "Sukses menambahkan Program baru");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal menambahkan " . $th->getMessage());
        }
    }

    public function update(Program $programPilihan, StoreProgramRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $programPilihan->update($data);

            DB::commit();
            return redirect()->back()->with('success', "Sukses mengubah data Program");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal mengubah data " . $th->getMessage());
        }
    }

    public function destroy(Program $programPilihan)
    {
        DB::beginTransaction();
        try {
            $programPilihan->delete($programPilihan);

            DB::commit();
            return redirect()->back()->with('success', "Sukses menghapus data Program");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal menghapus " . $th->getMessage());
        }
    }
}
