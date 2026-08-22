<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    /**
     * Tampilkan formulir pengaturan profil resmi kampus
     */
    public function index()
    {
        $user = Auth::user();
        
        $university = $user->university_id 
            ? University::find($user->university_id) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();

        if (!$university) {
            $university = University::create([
                'name' => $user->university ?? $user->name,
                'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $user->name), 0, 8)),
            ]);
            $user->update(['university_id' => $university->id]);
        }

        return view('university.profile.index', compact('user', 'university'));
    }

    /**
     * Simpan pembaruan profil resmi kampus & upload logo
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $university = $user->university_id 
            ? University::find($user->university_id) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();

        if (!$university) {
            $university = University::create([
                'name' => $request->name,
                'code' => $request->code,
            ]);
            $user->update(['university_id' => $university->id]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_nip' => 'nullable|string|max:50',
            'pic_position' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ], [
            'name.required' => 'Nama resmi universitas / perguruan tinggi wajib diisi.',
            'logo.image' => 'File logo harus berupa gambar (PNG, JPG, WEBP, atau SVG).',
            'logo.max' => 'Ukuran file logo maksimal 2MB.',
        ]);

        $data = [
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'pic_name' => $request->pic_name,
            'pic_nip' => $request->pic_nip,
            'pic_position' => $request->pic_position,
        ];

        // Handle upload logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $cleanCode = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->code ?? 'univ'));
            $filename = $cleanCode . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            $targetDir = public_path('images/logos');
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            $file->move($targetDir, $filename);
            $data['logo'] = 'images/logos/' . $filename;
        }

        $university->update($data);

        // Sinkronkan nama universitas pada user login
        $user->update([
            'university_id' => $university->id,
            'university' => $university->name,
        ]);

        return redirect()->route('university.profile.index')
            ->with('success', 'Profil Resmi Perguruan Tinggi & Kop Surat berhasil diperbarui!');
    }
}
