<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamData extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'date', 'time', 'duration', 'url', 'code'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
