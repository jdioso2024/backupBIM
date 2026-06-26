<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Model implements HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi1()
    {
        return $this->belongsTo(Prodi::class, 'prodi1_id');
    }

    public function prodi2()
    {
        return $this->belongsTo(Prodi::class, 'prodi2_id');
    }

    public function studentDocument()
    {
        return $this->hasOne(StudentDocument::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function jalurPendaftaran()
    {
        return $this->belongsTo(JalurPendaftaran::class);
    }

    public function prodi()
    {
        if ($this->status == 2) { // diterima1
            return $this->prodi1();
        } elseif ($this->status == 3) { // diterima2
            return $this->prodi2();
        }
        return null;
    }

    public function biodata()
    {
        return $this->hasOne(Biodata::class);
    }

    public function examData()
    {
        return $this->hasOne(ExamData::class);
    }

    public function registerPeriod()
    {
        return $this->belongsTo(RegisterPeriod::class);
    }

    /*
        0 => 'Berkas Belum Lengkap',
        1 => 'Slip Transfer Sudah dikonfirmasi',
        2 => 'Diterima Pilihan #1',
        3 => 'Diterima Pilihan #2',
        4 => 'Ditolak',
        5 => 'Lolos Seleksi Berkas jalur Beasiswa Parsial',
    */
}
