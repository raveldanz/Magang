{{--
    Baris keterangan kode status untuk form (<select> tidak bisa dua baris).
    <x-status-legend type="account" />
--}}
@props(['type' => 'application'])

@php $enum = \App\Enums\StatusType::enumFor($type); @endphp

<p {{ $attributes->class('mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-500') }}>
    @foreach ($enum::cases() as $case)
        <span><strong class="font-bold text-slate-700 tracking-wide">{{ $case->label() }}</strong> {{ $case->description() }}</span>
    @endforeach
</p>
