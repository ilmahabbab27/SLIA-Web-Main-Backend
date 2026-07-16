<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_type',
        'current_membership_type',
        'serial_no',
        'name',
        'name_with_initials',
        'gender',
        'picture',
        'nic',
        'nationality',
        'date_of_birth',
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
        'office_address',
        'office_phone',
        'office_email',
        'residence_address',
        'residence_phone',
        'residence_email',
        'contact_no',
        'email',
        'remarks',
        'is_active',
        'current_membership_status',
        'sort_order',
    ];

    protected $casts = [
        'serial_no' => 'integer',
        'date_of_birth' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
