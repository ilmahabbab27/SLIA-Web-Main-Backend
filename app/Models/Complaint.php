<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'name',
        'email',
        'category',
        'subject',
        'message',
        'attachment_path',
        'attachment_name',
        'status',
    ];
}
