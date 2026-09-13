<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = [
        'full_name', 'name_with_initials', 'yearbook_name', 'nic_number', 'gender', 'date_of_birth', 'photo',
        'membership_type', 'membership_number', 'membership_year', 'arb_number', 'academic_qualifications',
        'professional_qualifications', 'practice_name', 'practice_description', 'practice_type', 'location_district',
        'location_province', 'personal_website', 'office_address', 'office_district', 'office_province', 'office_phone',
        'office_fax', 'office_email', 'office_website', 'home_address', 'home_district', 'home_province', 'home_phone',
        'home_fax', 'home_email', 'contact_info_1_address', 'contact_info_1_phone', 'contact_info_1_email',
        'contact_info_2_address', 'contact_info_2_phone', 'contact_info_2_email', 'positions_held', 'slia_awards',
        'other_awards', 'username', 'password', 'source_data',
    ];

    public function up(): void
    {
        Schema::create('slia_member_directory', function (Blueprint $table) {
            $table->id();
            foreach ($this->fields as $field) {
                if ($field === 'full_name') {
                    $table->string($field, 191)->nullable();
                } elseif ($field === 'membership_number') {
                    $table->string($field, 255)->nullable();
                } else {
                    $table->longText($field)->nullable();
                }
            }
            $table->timestamps();
            $table->index('full_name', 'slia_directory_full_name_index');
            $table->index('membership_number', 'slia_directory_membership_number_index');
        });

        if (Schema::hasTable('slia_members')) {
            foreach (DB::table('slia_members')->get() as $member) {
                $data = (array) $member;
                unset($data['id']);
                $data['source_data'] = json_encode($data, JSON_UNESCAPED_UNICODE);
                DB::table('slia_member_directory')->insert($data);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('slia_member_directory');
    }
};
