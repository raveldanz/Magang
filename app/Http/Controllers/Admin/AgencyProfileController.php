<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use Illuminate\Http\Request;

class AgencyProfileController extends Controller
{
    // Display the agency profile edit form
    public function edit()
    {
        $profile = AgencyProfile::firstOrCreate(
            ['id' => 1],
            [
                'government_name' => 'Pemerintah Kota Surabaya',
                'agency_name' => 'Dinas Komunikasi Dan Informatika',
                'address' => 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'diskominfo@surabaya.go.id',
                'website' => 'https://diskominfo.surabaya.go.id',
                'signee_name' => 'Drs. H. M. NASER, M.Si',
                'signee_nip' => '19700101 199503 1 002',
                'signee_position' => 'Kepala Dinas Komunikasi dan Informatika',
                'city' => 'Surabaya',
            ]
        );

        return view('admin.agency_profile.edit', compact('profile'));
    }

    // Update the agency profile & upload logo
    public function update(Request $request)
    {
        $request->validate([
            'government_name' => 'required|string|max:255',
            'agency_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'signee_name' => 'required|string|max:255',
            'signee_nip' => 'nullable|string|max:100',
            'signee_position' => 'required|string|max:255',
            'city' => 'required|string|max:100',
        ]);

        $profile = AgencyProfile::firstOrCreate(['id' => 1]);

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
            $path = $request->file('logo')->store('images/logos', 'public');
            $data['logo'] = $path;
        }

        $profile->update($data);

        return redirect()->back()->with('success', 'Profil Instansi & Pengaturan TTD Surat berhasil diperbarui!');
    }
}
