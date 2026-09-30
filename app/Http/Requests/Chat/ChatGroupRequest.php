<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Buat grup (POST, wajib memilih anggota) atau ubah nama/deskripsi grup (PATCH).
 */
class ChatGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');

        return [
            'title' => ['required', 'string', 'min:3', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'member_ids' => [$creating ? 'required' : 'prohibited', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Nama grup wajib diisi.',
            'title.min' => 'Nama grup minimal :min karakter.',
            'title.max' => 'Nama grup maksimal :max karakter.',
            'description.max' => 'Deskripsi maksimal :max karakter.',
            'member_ids.required' => 'Pilih minimal satu anggota grup.',
            'member_ids.min' => 'Pilih minimal satu anggota grup.',
        ];
    }
}
