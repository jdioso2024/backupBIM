<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promoCodes = PromoCode::latest()->get();

        return view('pages.admrektorat.promo-code.index', compact('promoCodes'));
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
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'code' => 'required|string|max:255|unique:promo_codes,code',
            'description' => 'required|string|max:500',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_usage' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Simpan ke database
            $promo = PromoCode::create([
                'code' => $validatedData['code'],
                'description' => $validatedData['description'],
                'type' => $validatedData['type'],
                'value' => $validatedData['value'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'max_usage' => $validatedData['max_usage'],
                'is_active' => $validatedData['is_active'],
            ]);

            DB::commit();
            return redirect()->route('admrektorat.promo-code.index')->with('success', 'Kode promo berhasil dibuat!');
        } catch (\Exception $e) {
            // Jika terjadi error
            DB::rollBack();
            return redirect()->route('admrektorat.promo-code.index')->with('error', 'Terjadi kesalahan saat membuat kode promo!');
        }

        // Redirect atau response
    }


    /**
     * Display the specified resource.
     */
    public function show(PromoCode $promoCode)
    {
        $promoCode = $promoCode->load('usages');

        return view('pages.admrektorat.promo-code.show', compact('promoCode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PromoCode $promoCode)
    {
        DB::beginTransaction();
        try {
            $promoCode->delete();
            DB::commit();
            return redirect()->route('admrektorat.promo-code.index')->with('success', 'Kode promo berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admrektorat.promo-code.index')->with('error', 'Terjadi kesalahan saat menghapus kode promo!');
        }
    }
}
