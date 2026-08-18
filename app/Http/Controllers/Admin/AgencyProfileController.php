<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AgencyProfileController extends Controller
{
    // Helper to determine if the logged in user is Superadmin
    private function isSuperAdmin($user): bool
    {
        return $user && ($user->email === 'admin@gmail.com' || $user->agency_profile_id === null);
    }

    // Display the agency profile edit form
    public function edit(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $this->isSuperAdmin($user);

        if ($isSuperAdmin) {
            // Superadmin can view & switch any agency
            $requestedId = $request->query('agency_id', $request->query('id', 1));
            $agencyProfile = AgencyProfile::find($requestedId) ?? AgencyProfile::firstOrCreate(['id' => $requestedId]);
            $allAgencies = AgencyProfile::all();
        } else {
            // Regular Agency Admin: restricted strictly to own agency
            $userAgencyId = $user->agency_profile_id;
            
            // Check if user tries to access another agency ID via query parameter
            $requestedId = $request->query('agency_id', $request->query('id'));
            if ($requestedId && (int)$requestedId !== (int)$userAgencyId) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengubah instansi lain.');
            }

            $agencyProfile = $user->agencyProfile ?? AgencyProfile::findOrFail($userAgencyId);
            $allAgencies = collect([$agencyProfile]);
        }

        $profile = $agencyProfile; // for backwards compatibility in views

        return view('admin.agency_profile.edit', compact('agencyProfile', 'profile', 'allAgencies', 'isSuperAdmin'));
    }

    // Update the agency profile & upload logo
    public function update(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $this->isSuperAdmin($user);

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

        if ($isSuperAdmin) {
            $agencyId = $request->input('agency_id', 1);
            $profile = AgencyProfile::firstOrCreate(['id' => $agencyId]);
        } else {
            $userAgencyId = $user->agency_profile_id;
            $submittedAgencyId = $request->input('agency_id');
            if ($submittedAgencyId && (int)$submittedAgencyId !== (int)$userAgencyId) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengubah instansi lain.');
            }
            $profile = $user->agencyProfile ?? AgencyProfile::findOrFail($userAgencyId);
        }

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

