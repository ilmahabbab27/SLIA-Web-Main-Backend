<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PabEvent extends Model
{
    protected $fillable = ['title', 'description', 'event_date', 'event_time', 'venue', 'link', 'file_name', 'file_src', 'image_name', 'image_src', 'is_active', 'sort_order'];
}
