<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BapSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'introduction',
    ];
}
