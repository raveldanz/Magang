<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

/**
 * Pendaftaran akun mandiri: Peserta Magang (mahasiswa/siswa) atau Pembimbing (dosen/guru).
 */
class RegisterRequest extends FormRequest
{
    /**
     * Penyedia email pribadi yang ditolak saat Pembimbing (dosen/guru) daftar mandiri (gmail.com,
     * yahoo.co.id, dst.). Peserta magang boleh memakai email pribadi karena banyak siswa SMA/SMK
     * tidak punya email sekolah. Hanya label pertama setelah "@" yang dicocokkan, sehingga
     * subdomain kampus seperti mail.ugm.ac.id atau live.undip.ac.id tetap diterima.
     */
    private const PERSONAL_EMAIL_DOMAIN = '/@(gmail|googlemail|yahoo|ymail|rocketmail|hotmail|outlook|live|msn|icloud|me|aol|mail|protonmail|proton|yandex|gmx)\.[a-z]{2,}(\.[a-z]{2})?$/i';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'name' => trim((string) $this->input('name')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'register_as' => ['nullable', 'in:student,advisor'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class,
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->registersAdvisor() && is_string($value) && preg_match(self::PERSONAL_EMAIL_DOMAIN, $value)) {
                        $fail('Akun Pembimbing wajib memakai email resmi kampus (.ac.id), sekolah (.sch.id), atau instansi (.go.id).');
                    }
                },
            ],
            'university' => ['nullable', 'required_if:register_as,advisor', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'register_as.in' => 'Pilih jenis akun: Peserta Magang atau Pembimbing.',
            'university.required_if' => 'Nama Perguruan Tinggi atau Sekolah asal wajib diisi untuk akun Pembimbing.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk, atau gunakan Lupa Kata Sandi.',
        ];
    }

    public function registersAdvisor(): bool
    {
        return $this->input('register_as') === 'advisor';
    }
}
