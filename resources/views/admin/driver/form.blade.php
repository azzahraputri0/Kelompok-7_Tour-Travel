@extends('layouts.app',['title'=>($driver ? 'Form Edit Driver' : 'Form Tambah Driver')])
@section('content')
@include('admin.partials.form',['resource'=>'driver','item'=>$driver])
@endsection