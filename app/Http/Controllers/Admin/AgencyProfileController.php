<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgencyProfileController extends Controller
{
    // Display the agency profile edit form
    public function edit(Request $request)
    {
        $defaultAgencyId = auth()->user()?->agency_profile_id ?? 1;
        $agencyId = $request->query('id', $defaultAgencyId);
        
        $profile = AgencyProfile::find($agencyId) ?? AgencyProfile::firstOrCreate(
            ['id' => $agencyId],
            [
                'government_name' => 'Pemerintah Kota Surabaya',
                'agency_name' => 'Dinas Komunikasi Dan Informatika',
                'address' => 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'diskominfo@surabaya.go.id',
                'website' => 'https://diskominfo.surabaya.go.id',
                'logo' => 'images/logos/diskominfo.png',
                'signee_name' => 'Drs. H. M. NASER, M.Si',
                'signee_nip' => '19700101 199503 1 002',
                'signee_position' => 'Kepala Dinas Komunikasi dan Informatika',
                'city' => 'Surabaya',
            ]
        );

        $allAgencies = AgencyProfile::all();

        return view('admin.agency_profile.edit', compact('profile', 'allAgencies'));
    }


    // Update the agency profile & upload logo
    public function update(Request $request)
    {
        $request->validate([
            'agency_id'       => 'nullable|exists:agency_profiles,id',
            'government_name' => 'required|string|max:255',
            'agency_name'     => 'required|string|max:255',
            'address'         => 'nullable|string',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:100',
            'website'         => 'nullable|string|max:150',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'signee_name'     => 'required|string|max:255',
            'signee_nip'      => 'nullable|string|max:100',
            'signee_position' => 'required|string|max:255',
            'city'            => 'required|string|max:100',
        ]);

        $agencyId = $request->input('agency_id', 1);
        $profile = AgencyProfile::firstOrCreate(['id' => $agencyId]);

        $data = $request->only([
            'government_name',
            'agency_name',
            'address',
            'phone',
            'email',
            'website',
            'signee_name',
            'signee_nip',
            'signee_position',
            'city',
        ]);

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada pada disk publik
            if (!empty($profile->logo) && Storage::disk('public')->exists($profile->logo)) {
                Storage::disk('public')->delete($profile->logo);
            }

            // Simpan logo baru ke disk publik
            $path = $request->file('logo')->store('images/logos', 'public');
            $data['logo'] = $path;
        }

        $profile->update($data);

        return redirect()->back()->with('success', 'Profil Instansi & Pengaturan TTD Surat berhasil diperbarui!');
    }
}
