<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'label',
        'icon',
        'text',
        'href',
        'is_active',
        'is_locked',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];
}
