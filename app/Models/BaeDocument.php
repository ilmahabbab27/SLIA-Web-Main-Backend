<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'application_section',
        'title',
        'meta',
        'file_name',
        'file_src',
        'external_url',
        'uploaded_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
