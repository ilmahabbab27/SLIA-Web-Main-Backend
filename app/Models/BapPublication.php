<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BapPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'volume',
        'uploaded_at',
        'cover_src',
        'file_name',
        'file_src',
        'external_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
