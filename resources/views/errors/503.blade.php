@extends('errors.minimal')

@section('code', '503')
@section('title', 'Sistem Sedang Dalam Pemeliharaan')
@section('message', 'Layanan dihentikan sementara. Silakan coba lagi beberapa saat lagi.')

@section('actions')
    <button type="button" class="tombol utama" onclick="location.reload()">Muat Ulang Halaman</button>
@endsection
