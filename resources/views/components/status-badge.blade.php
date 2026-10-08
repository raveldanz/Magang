{{--
    Badge status (pengajuan, review logbook/laporan, akun, feedback) — satu-satunya tempat nama & warna status dirender, di semua role.
    Nama = kode sistem (Enum::label), keterangan = Enum::description (lihat App\Enums\StatusType).

    <x-status-badge :status="$app->status" />          ringkas: tabel & area sempit, keterangan jadi tooltip
                                                        (layar sentuh tanpa hover: keterangan tampil di baris kedua badge)
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
    $code = strtolower($case?->value ?? $raw);
    $pill = "inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold tracking-wide rounded-full border whitespace-nowrap {$tone}";

    $isSuccess = in_array($code, ['approved', 'active', 'accepted', 'completed', 'verified', 'resolved', 'closed'], true);
    $isDanger  = in_array($code, ['rejected', 'inactive'], true);
    $isWarning = in_array($code, ['pending', 'waiting'], true);
    $isInfo    = in_array($code, ['revision', 'in_progress'], true);
    $isNeutral = in_array($code, ['resigned', 'cancelled'], true);

    $iconSvg = match (true) {
        $isSuccess => '<svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/></svg>',
        $isDanger  => '<svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M3.72 3.72a.75.75 0 0 1 1.06 0L8 6.94l3.22-3.22a.75.75 0 1 1 1.06 1.06L9.06 8l3.22 3.22a.75.75 0 1 1-1.06 1.06L8 9.06l-3.22 3.22a.75.75 0 0 1-1.06-1.06L6.94 8 3.72 4.78a.75.75 0 0 1 0-1.06Z"/></svg>',
        $isWarning => '<svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0v4.25c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5V3.75Z" clip-rule="evenodd"/></svg>',
        $isInfo    => '<svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M13.836 2.477a.75.75 0 0 1 .75.75v3.182a.75.75 0 0 1-.75.75h-3.182a.75.75 0 0 1 0-1.5h1.9a5.002 5.002 0 0 0-8.65 1.622.75.75 0 0 1-1.458-.352 6.502 6.502 0 0 1 11.34-2.122V3.227a.75.75 0 0 1 .75-.75Zm-11.672 8a.75.75 0 0 1 .75-.75h3.182a.75.75 0 0 1 0 1.5h-1.9a5.002 5.002 0 0 0 8.65-1.622.75.75 0 0 1 1.458.352 6.502 6.502 0 0 1-11.34 2.122v.576a.75.75 0 0 1-1.5 0v-3.182a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>',
        $isNeutral => '<svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd"/></svg>',
        default    => '<svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v9A1.5 1.5 0 0 0 4.5 14h7a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 11.5 2h-7Zm3.25 3.5a.75.75 0 0 0-1.5 0v3.5a.75.75 0 0 0 1.5 0V5.5Zm-.75 6.75a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z" clip-rule="evenodd"/></svg>',
    };
@endphp

@if ($stacked)
    <div {{ $attributes->class('inline-flex flex-col items-start gap-1')->merge(['data-status' => $case?->value ?? $raw]) }}>
        <span class="{{ $pill }}" @if ($tooltip) title="{{ $tooltip }}" @endif>
            {!! $iconSvg !!}
            {{ $text }}
        </span>
        @if ($case)
            <span class="text-xs font-medium text-slate-500 leading-snug hidden sm:block">{{ $case->description() }}</span>
        @endif
    </div>
@else
    <span {{ $attributes->class($pill)->merge(['data-status' => $case?->value ?? $raw, 'title' => $tooltip ?: $case?->description()]) }}>
        {!! $iconSvg !!}
        {{ $text }}
    </span>
@endif
