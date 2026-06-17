<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnualEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'event_type',
        'title',
        'subtitle',
        'description',
        'image',
        'images',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
    ];
}
