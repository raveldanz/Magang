@extends('errors.minimal')

@section('code', '401')
@section('title', 'Anda Belum Masuk')
@section('message', 'Silakan masuk terlebih dahulu untuk membuka halaman ini.')

@section('actions')
    <a class="tombol utama" href="/login">Masuk ke Portal</a>
@endsection
