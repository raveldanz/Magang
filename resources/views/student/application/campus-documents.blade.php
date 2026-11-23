@foreach (['surat_pengantar' => 'Surat Pengantar', 'proposal_kampus' => 'Proposal Kampus'] as $field => $label)
    <div class="space-y-2 rounded-xl border border-slate-200 p-4">
        <label for="{{ $scope }}_{{ $field }}" class="block text-sm font-semibold text-slate-800">
            {{ $label }} {{ $required ? '(wajib)' : '(opsional)' }}
        </label>
        <input type="file" id="{{ $scope }}_{{ $field }}" name="{{ $field }}"
               accept=".pdf" data-max-kb="{{ \App\Support\UploadRules::MAX_DOCUMENT_KB }}" @required($required)
               aria-describedby="{{ $scope }}_{{ $field }}_hint"
               class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700">
        <p id="{{ $scope }}_{{ $field }}_hint" class="text-xs text-slate-500">PDF, maksimal 2 MB.</p>
        <x-input-error :data-inline-for="$field" :messages="$errors->get($field)" class="mt-1" />
        @if ($scope === 'revisi')
            @php($currentDocument = $rejectedGroupApplication->documents->firstWhere('document_type', $label))
            @if ($currentDocument)
                <a class="text-xs text-blue-700 underline" href="{{ route('documents.application', $currentDocument) }}" target="_blank">Lihat {{ $label }} saat ini</a>
            @elseif ($field === 'surat_pengantar' && $rejGroup->proposal_letter_path)
                <a class="text-xs text-blue-700 underline" href="{{ route('documents.group.proposal', $rejGroup) }}" target="_blank">Lihat berkas kelompok sebelumnya</a>
            @endif
        @endif
    </div>
@endforeach
