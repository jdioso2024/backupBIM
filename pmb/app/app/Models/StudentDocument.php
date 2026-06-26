<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'slip_transfer',
        'ktp',
        'kartu_keluarga',
        'akta_lahir',
        'ijazah',
        'prestasi',
        'pas_foto',
        'nilai_rapot',
        'cv',
        'surat_rekomendasi',
        'esai',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
