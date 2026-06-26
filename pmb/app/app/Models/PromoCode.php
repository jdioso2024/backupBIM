<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;
    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'start_date',
        'end_date',
        'max_usage',
        'usage_count',
        'is_active',
    ];

    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }
}
