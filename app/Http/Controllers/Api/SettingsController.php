<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return Setting::all();
    }

    public function update(Request $request, $key)
    {
        if ($key === 'site_logo') {
            // Validasi file logo
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Ambil logo lama
            $oldLogo = Setting::where('key', 'site_logo')->value('value');

            // Nama file baru (biar unik)
            $filename = time() . '.' . $request->file('logo')->getClientOriginalExtension();

            // Pindahkan ke folder public/images
            $request->file('logo')->move(public_path('images'), $filename);

            // Buat path yang disimpan di DB
            $newPath = '/images/' . $filename;

            // Simpan ke DB
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $newPath]);

            // Hapus logo lama jika ada
            if ($oldLogo && file_exists(public_path($oldLogo))) {
                @unlink(public_path($oldLogo));
            }

            return response()->json([
                'message' => 'Logo updated',
                'value' => $newPath,
            ]);
        }

        if ($key === 'site_background') {
            // Validasi file background (wajib)
            $request->validate([
                'background' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            ]);

            // Ambil background lama
            $oldBg = Setting::where('key', 'site_background')->value('value');

            // Upload background baru (ke public/images)
            $filename = time() . '.' . $request->file('background')->getClientOriginalExtension();
            $request->file('background')->move(public_path('images'), $filename);

            $newPath = '/images/' . $filename;

            // Simpan ke DB
            Setting::updateOrCreate(['key' => 'site_background'], ['value' => $newPath]);

            // Hapus background lama jika ada
            if ($oldBg && file_exists(public_path($oldBg))) {
                @unlink(public_path($oldBg));
            }

            return response()->json([
                'message' => 'Background updated',
                'value' => $newPath,
            ]);
        }

        // Validasi untuk field value biasa
        $validated = $request->validate([
            'value' => 'required',
        ]);

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $validated['value']]
        );

        return response()->json([
            'message' => 'Setting updated',
            'key' => $key,
            'value' => $validated['value'],
        ]);
    }
}
