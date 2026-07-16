<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicCalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_date',
        'event_time',
        'venue',
        'description',
    ];
}
