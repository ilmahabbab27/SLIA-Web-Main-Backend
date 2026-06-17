<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwardSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'general_guideline',
    ];
}
