<?php

namespace App\Http\Requests\Chat;

use App\Services\Chat\ChatAttachmentStorage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $extensions = implode(',', ChatAttachmentStorage::allowedExtensions());

        return [
            'body' => ['nullable', 'string', 'max:' . (int) config('chat.max_body_length', 5000)],
            'reply_to_id' => ['nullable', 'integer', 'min:1'],
            'voice' => ['nullable', 'boolean'],
            'files' => ['nullable', 'array', 'max:' . (int) config('chat.max_files_per_message', 5)],
            // `mimes` memeriksa jenis isi file (bukan sekadar nama), `extensions` memeriksa nama unggahan
            'files.*' => ['file', 'max:' . ChatAttachmentStorage::maxUploadKb(), "mimes:$extensions", "extensions:$extensions"],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (trim((string) $this->input('body')) === '' && empty($this->file('files'))) {
                    $validator->errors()->add('body', 'Tulis pesan atau lampirkan file terlebih dahulu.');
                }
            },
        ];
    }

    public function messages(): array
    {
        $maxMb = number_format(ChatAttachmentStorage::maxUploadKb() / 1024, 1, ',', '.');
        $unsupported = 'Jenis file tidak didukung. Gunakan foto, video, audio, PDF, dokumen Office, TXT/CSV, atau ZIP/RAR.';

        return [
            'body.max' => 'Pesan terlalu panjang (maksimal :max karakter).',
            'files.max' => 'Maksimal :max lampiran dalam satu pesan.',
            'files.*.max' => "Ukuran setiap file maksimal {$maxMb} MB.",
            'files.*.mimes' => $unsupported,
            'files.*.extensions' => $unsupported,
            'files.*.uploaded' => "File gagal diunggah. Pastikan ukurannya tidak melebihi {$maxMb} MB.",
        ];
    }

    /**
     * @return \Illuminate\Http\UploadedFile[]
     */
    public function attachments(): array
    {
        return array_values(array_filter((array) $this->file('files', [])));
    }
}
