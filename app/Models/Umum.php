<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Umum extends Authenticatable { protected $table='UMUM'; protected $primaryKey='id_pengguna'; public $incrementing=false; protected $keyType='string'; public $timestamps=false; protected $hidden=['password']; protected $fillable=['id_pengguna','nama_lengkap','email','nomor_telepon','password','role','nomor_identitas','dokumen_identitas']; public function getAuthPassword(){return $this->password;} }
