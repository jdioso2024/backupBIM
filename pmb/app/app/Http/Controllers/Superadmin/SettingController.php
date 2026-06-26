<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'formulir_description' => Setting::get('formulir_description'),
            'alur_pendaftaran'     => json_decode(Setting::get('alur_pendaftaran', '[]'), true) ?? [],
            'site_logo'            => Setting::get('site_logo'),
            'brosur'               => json_decode(Setting::get('brosur', '[]'), true) ?? [],
            'jalur_masuk'          => json_decode(Setting::get('jalur_masuk', '[]'), true) ?? [],
            'wa_admin'             => Setting::get('wa_admin', '628813709234'),
            'catatan_pendaftaran'  => Setting::get('catatan_pendaftaran', ''),
            'tampilkan_form'       => Setting::get('tampilkan_form', '1'),
        ];
        return view('pages.superadmin.setting.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'formulir_description'  => 'required|string',
            'alur_pendaftaran'      => 'required|array|min:1',
            'alur_pendaftaran.*'    => 'required|string',
            'logo'                  => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'brosur'                => 'nullable|array',
            'brosur.*.label'        => 'required_with:brosur|string',
            'brosur.*.url'          => 'required_with:brosur|url',
            'jalur_masuk'           => 'nullable|array',
            'jalur_masuk.*.title'   => 'required_with:jalur_masuk|string',
            'jalur_masuk.*.desc'    => 'required_with:jalur_masuk|string',
            'wa_admin'              => 'nullable|string|max:20',
            'catatan_pendaftaran'   => 'nullable|string',
        ]);

        Setting::set('formulir_description', $request->formulir_description);
        Setting::set('wa_admin', $request->wa_admin ?? '');
        Setting::set('catatan_pendaftaran', $request->catatan_pendaftaran ?? '');
        Setting::set('tampilkan_form', $request->has('tampilkan_form') ? '1' : '0');

        $steps = array_map(fn($t) => ['text' => $t], $request->alur_pendaftaran);
        Setting::set('alur_pendaftaran', json_encode($steps));

        Setting::set('brosur', json_encode($request->brosur ?? []));

        Setting::set('jalur_masuk', json_encode($request->jalur_masuk ?? []));

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('site_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('logo')->store('logo', 'public');
            Setting::set('site_logo', $path);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
