@extends('layouts.app',['title'=>'Data Master'])
@section('content')
@include('admin.partials.list', ['resource'=>'kendaraan','items'=>$daftarKendaraan])
@endsection