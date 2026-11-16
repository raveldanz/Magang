@extends('errors.minimal')

@section('code', '429')
@section('title', 'Terlalu Banyak Permintaan')
@section('message', 'Anda mengirim permintaan terlalu cepat. Tunggu sekitar satu menit, lalu coba lagi.')

@section('actions')
    <button type="button" class="tombol utama" onclick="location.reload()">Muat Ulang Halaman</button>
@endsection
