<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'images',
        'title',
        'subtitle',
        'description',
        'date',
        'venue',
        'link',
        'category',
        'show_latest',
        'show_upcoming',
        'show_past',
        'show_program',
        'show_highlight',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'images' => 'array',
        'show_latest' => 'boolean',
        'show_upcoming' => 'boolean',
        'show_past' => 'boolean',
        'show_program' => 'boolean',
        'show_highlight' => 'boolean',
    ];
}
