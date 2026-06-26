<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterPeriod extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'start_date', 'end_date', 'is_active'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function getStatusAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
