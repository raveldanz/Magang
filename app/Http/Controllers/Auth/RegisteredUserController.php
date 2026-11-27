<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\University;
use App\Models\User;
use App\Services\UniversityResolver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Daftar akun mandiri sebagai Peserta Magang atau Pembimbing (dosen/guru).
     *
     * Pembimbing tidak perlu didaftarkan admin, tetapi akunnya berstatus Menunggu Persetujuan sampai
     * Admin Kampus/Sekolah atau Super Admin menyetujuinya (AdvisorApprovalService), agar tidak ada
     * akun pembimbing palsu yang dipilih mahasiswa untuk menilai dirinya sendiri.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $isAdvisor = $request->registersAdvisor();

        $user = DB::transaction(function () use ($request, $isAdvisor) {
            $data = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'role' => $isAdvisor ? 'dosen' : 'mahasiswa',
            ];

            if ($isAdvisor) {
                // Nama/akronim yang dikenal (mis. "unesa") dipakai ulang; nama baru masuk daftar verifikasi Super Admin.
                $name = $request->validated('university');
                $id = $request->validated('selected_institution_id');
                $institution = $id ? University::whereKey($id)->where('is_verified', true)->where('status', 'active')->where('name', $name)->first() : null;
                if ($id && ! $institution) {
                    throw ValidationException::withMessages(['university' => 'Pilihan institusi berubah. Silakan pilih kembali.']);
                }
                $institution ??= $this->advisorInstitution($name);
                $data['university_id'] = $institution->id;
                $data['university'] = $institution->name;
                $data['status'] = AccountStatus::PENDING->value;
            }

            return User::create($data);
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        if ($isAdvisor) {
            return redirect()->route('dashboard')->with('success', 'Akun Pembimbing berhasil dibuat. Akun Anda sedang menunggu persetujuan Admin Kampus/Sekolah. Setelah disetujui, peserta magang dapat memilih Anda sebagai pembimbing.');
        }

        return redirect()->route('student.profile.edit')->with('success', 'Akun berhasil dibuat! Silakan lengkapi profil Anda.');
    }

    /**
     * Institusi akun Pembimbing. Bila beberapa institusi bernama sama, kota wajib disebut ("Nama · Kota")
     * agar akun tidak menempel ke sekolah yang salah. Kota yang tidak cocok menjadi data baru untuk diverifikasi.
     *
     * @throws ValidationException
     */
    private function advisorInstitution(string $typed): University
    {
        [$name, $city] = array_pad(array_map('trim', explode('·', $typed, 2)), 2, '');
        $same = app(UniversityResolver::class)->sameNamed($name);

        if ($city === '' && $same->count() > 1) {
            $options = $same->map(fn (University $u) => $u->cityLabel())->filter()->unique()
                ->take(15)->map(fn (string $label) => "\"{$name} · {$label}\"")->implode(', ');

            throw ValidationException::withMessages([
                'university' => "Ada beberapa sekolah/kampus bernama \"{$name}\". Tambahkan nama kota setelah tanda ·"
                    .($options !== '' ? ", contoh: {$options}." : '.'),
            ]);
        }

        if ($city !== '') {
            $inCity = $same->filter(fn (University $u) => UniversityResolver::normalize($u->cityLabel()) === UniversityResolver::normalize($city));
            if ($inCity->count() === 1) {
                return University::findOrFail($inCity->first()->id);
            }
        }

        return University::findOrCreateUnverified($typed);
    }
}
