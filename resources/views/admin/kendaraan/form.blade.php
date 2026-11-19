@extends('layouts.app',['title'=>($kendaraan ? 'Form Edit Kendaraan' : 'Form Tambah Kendaraan')])
@section('content')
@include('admin.partials.form',['resource'=>'kendaraan','item'=>$kendaraan])
@endsection