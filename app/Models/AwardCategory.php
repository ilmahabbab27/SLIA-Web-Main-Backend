<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwardCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'name',
        'description',
        'guideline',
        'guideline_attachment_name',
        'guideline_attachment',
        'general_guideline',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
