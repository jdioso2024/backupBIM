<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdiRequest;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramStudiController extends Controller
{
    public function index()
    {
        $programs = Prodi::latest()->get();
        return view('pages.superadmin.program-studi.index', compact('programs'));
    }

    public function store(StoreProdiRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            Prodi::create($data);

            DB::commit();
            return redirect()->back()->with('success', "Sukses menambahkan Program Studi baru");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal menambahkan " . $th->getMessage());
        }
    }

    public function update(Prodi $programStudi, StoreProdiRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $programStudi->update($data);

            DB::commit();
            return redirect()->back()->with('success', "Sukses mengubah data Program Studi");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal mengubah data " . $th->getMessage());
        }
    }

    public function destroy(Prodi $programStudi)
    {
        DB::beginTransaction();
        try {
            $programStudi->delete($programStudi);

            DB::commit();
            return redirect()->back()->with('success', "Sukses menghapus data Program Studi");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal menghapus " . $th->getMessage());
        }
    }
}
