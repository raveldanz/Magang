@extends('errors.minimal')

@section('code', '500')
@section('title', 'Terjadi Gangguan pada Sistem')
@section('message', 'Permintaan Anda belum dapat diproses. Coba muat ulang halaman. Jika masih terjadi, hubungi admin dan sebutkan apa yang sedang Anda lakukan.')

@section('actions')
    <button type="button" class="tombol utama" onclick="location.reload()">Muat Ulang Halaman</button>
@endsection
