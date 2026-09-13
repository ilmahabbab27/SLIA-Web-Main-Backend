<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SliaMember extends Model
{
    protected $table = 'slia_member_directory';

    protected $fillable = [
        'full_name',
        'name_with_initials',
        'yearbook_name',
        'nic_number',
        'gender',
        'date_of_birth',
        'photo',
        'membership_type',
        'membership_number',
        'membership_year',
        'arb_number',
        'academic_qualifications',
        'professional_qualifications',
        'practice_name',
        'practice_description',
        'practice_type',
        'location_district',
        'location_province',
        'personal_website',
        'office_address',
        'office_district',
        'office_province',
        'office_phone',
        'office_fax',
        'office_email',
        'office_website',
        'home_address',
        'home_district',
        'home_province',
        'home_phone',
        'home_fax',
        'home_email',
        'slia_contact_email',
        'contact_info_1_address',
        'contact_info_1_phone',
        'contact_info_1_email',
        'contact_info_2_address',
        'contact_info_2_phone',
        'contact_info_2_email',
        'positions_held',
        'slia_awards',
        'other_awards',
        'username',
        'password',
        'source_data',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'source_data',
    ];
}
