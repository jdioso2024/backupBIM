<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Biodata extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    protected $fillable = [
        'student_id',
        'name',
        'nomor_hp',
        'alamat',
        'tanggal_lahir',
        'nik',
        'nama_orangtua',
        'nomor_hp_orangtua',
        'nik_orangtua',
        'hubungan',
        'tempat_lahir',
        'jenis_kelamin',
        'asal_sekolah',
        'nisn',
        'no_kip',
        'parent_work',
        'parent_income',
        'emergency_contact',
        'reason_scholarship',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('daftarulang')
        ->useFallbackUrl(asset('img/truck.png'))
        ->registerMediaConversions(function (Media $media) {
            $this->addMediaConversion('thumb')
            ->width(800)
                ->height(800)
                ->sharpen(10)
                ->optimize()
                ->performOnCollections('daftarulang');
        });

        $this->addMediaCollection('public_files');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
