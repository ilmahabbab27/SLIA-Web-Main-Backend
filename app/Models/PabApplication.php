<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PabApplication extends Model
{
    protected $fillable = ['title', 'description', 'file_name', 'file_src', 'is_active', 'sort_order'];
}
