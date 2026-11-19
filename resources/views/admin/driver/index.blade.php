@extends('layouts.app',['title'=>'Data Master'])
@section('content')
@include('admin.partials.list', ['resource'=>'driver','items'=>$daftarDriver])
@endsection