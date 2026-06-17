<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_type',
        'serial_no',
        'name',
        'academic_qualifications',
        'membership_year',
        'membership_number',
        'student_membership_number',
        'student_membership_year',
        'graduate_membership_number',
        'graduate_membership_year',
        'associate_membership_number',
        'associate_membership_year',
        'address',
        'contact_no',
        'email',
        'remarks',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'serial_no' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
