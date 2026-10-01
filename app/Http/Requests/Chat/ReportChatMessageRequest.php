<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(config('chat.report_reasons', [])))],
            'note' => ['nullable', 'string', 'max:1000', 'required_if:reason,lainnya'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Pilih alasan pelaporan.',
            'reason.in' => 'Alasan pelaporan tidak valid.',
            'note.required_if' => 'Jelaskan alasan pelaporan Anda.',
            'note.max' => 'Catatan maksimal :max karakter.',
        ];
    }
}
