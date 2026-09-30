{{--
    Badge status (pengajuan, review logbook/laporan, akun, feedback) — satu-satunya tempat nama & warna status dirender, di semua role.
    Nama = kode sistem (Enum::label), keterangan = Enum::description (lihat App\Enums\StatusType).

    <x-status-badge :status="$app->status" />          ringkas: tabel & area sempit, keterangan jadi tooltip
    <x-status-badge :status="$app->status" stacked />  bertingkat: kartu/panel lapang, keterangan di baris kedua
    :tooltip="..."                                      ganti isi tooltip (mis. alasan penolakan)
    type="review" | "account" | "feedback"            status review logbook/laporan, akun pengguna, tiket feedback
--}}
@props(['status' => null, 'stacked' => false, 'tooltip' => null, 'type' => 'application'])

@php
    $enum = \App\Enums\StatusType::enumFor($type);
    $case = $enum::resolve($status);
    $raw = $status instanceof \BackedEnum ? (string) $status->value : (string) ($status ?? '');
    $text = $case?->label() ?? ($raw !== '' ? strtoupper($raw) : '-');
    $tone = $case?->badgeColor() ?? 'bg-slate-100 text-slate-700 border-slate-300';
    $dot = $case?->dotColor() ?? 'bg-slate-400';
    $pill = "inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold tracking-wide rounded-full border whitespace-nowrap {$tone}";
@endphp

@if ($stacked)
    <div {{ $attributes->class('inline-flex flex-col items-start gap-1')->merge(['data-status' => $case?->value ?? $raw]) }}>
        <span class="{{ $pill }}" @if ($tooltip) title="{{ $tooltip }}" @endif>
            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dot }}"></span>
            {{ $text }}
        </span>
        @if ($case)
            <span class="text-xs font-medium text-slate-500 leading-snug">{{ $case->description() }}</span>
        @endif
    </div>
@else
    <span {{ $attributes->class($pill)->merge(['data-status' => $case?->value ?? $raw, 'title' => $tooltip ?: $case?->description()]) }}>
        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dot }}"></span>
        {{ $text }}
    </span>
@endif
