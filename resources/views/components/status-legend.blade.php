{{--
    Baris keterangan kode status untuk form (<select> tidak bisa dua baris).
    <x-status-legend type="account" />
--}}
@props(['type' => 'application'])

@php
    $enum = \App\Enums\StatusType::enumFor($type);
    $userRole = auth()->user()?->role;
    $useIndonesianUi = \App\Models\User::isLocalizedStatusUiRole($userRole);
@endphp

<p {{ $attributes->class('mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600') }}>
    @foreach ($enum::cases() as $case)
        <span><strong class="font-bold text-slate-800 tracking-wide">{{ $useIndonesianUi ? $case->localizedLabel($userRole) : $case->label() }}</strong> {{ $case->description($userRole) }}</span>
    @endforeach
</p>
