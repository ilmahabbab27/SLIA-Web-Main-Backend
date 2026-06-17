<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'introduction_title',
        'introduction',
        'focus_points',
    ];

    protected $casts = [
        'focus_points' => 'array',
    ];
}
