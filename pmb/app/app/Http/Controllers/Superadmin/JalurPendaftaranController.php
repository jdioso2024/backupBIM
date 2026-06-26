<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJalurPendaftaranRequest;
use App\Models\JalurPendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JalurPendaftaranController extends Controller
{
    public function index()
    {
        $jalurs = JalurPendaftaran::latest()->get();
        return view('pages.superadmin.jalur-pendaftaran.index', compact('jalurs'));
    }

    public function store(StoreJalurPendaftaranRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            JalurPendaftaran::create($data);

            DB::commit();
            return redirect()->back()->with('success', "Sukses menambahkan jalur pendaftaran baru");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal menambahkan " . $th->getMessage());
        }
    }

    public function update(JalurPendaftaran $jalurPendaftaran, StoreJalurPendaftaranRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $jalurPendaftaran->update($data);

            DB::commit();
            return redirect()->back()->with('success', "Sukses mengubah data jalur pendaftaran");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal mengubah data " . $th->getMessage());
        }
    }

    public function destroy(JalurPendaftaran $jalurPendaftaran)
    {
        DB::beginTransaction();
        try {
            $jalurPendaftaran->delete($jalurPendaftaran);

            DB::commit();
            return redirect()->back()->with('success', "Sukses menghapus data jalur pendaftaran");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', "Gagal menghapus " . $th->getMessage());
        }
    }
}
