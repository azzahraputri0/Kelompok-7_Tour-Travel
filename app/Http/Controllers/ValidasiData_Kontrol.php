<?php

namespace App\Http\Controllers;

use App\Entities\Pemesanan_Entity;
use App\Models\Umum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ValidasiData_Kontrol extends AuthController
{
    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $r)
    {
        $d = $r->validate(['nama_lengkap' => 'required|string|max:50', 'email' => 'required|email|max:50|unique:UMUM,email', 'nomor_telepon' => 'required|string|max:50', 'password' => 'required|string|min:8|confirmed', 'nomor_identitas' => 'nullable|digits:16', 'dokumen_identitas' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096']);
        if ($r->hasFile('dokumen_identitas')) {
            $d['dokumen_identitas'] = $r->file('dokumen_identitas')->store('identitas', 'local');
        }$d['password'] = Hash::make($d['password']);
        Umum::create($d + ['id_pengguna' => Pemesanan_Entity::id(), 'role' => 'Customer']);

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil. Silakan masuk.');
    }
}
