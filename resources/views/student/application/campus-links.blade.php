@foreach ($application->documents->whereIn('document_type', ['Surat Pengantar', 'Proposal Kampus']) as $document)
    <a class="block text-xs font-semibold text-blue-700 underline" href="{{ route('documents.application', $document) }}" target="_blank">{{ $document->document_type }}</a>
@endforeach
