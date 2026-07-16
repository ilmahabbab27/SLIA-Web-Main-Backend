<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slia_members', function (Blueprint $table) {
            $table->id();

            // Personal Details
            $table->string('full_name');
            $table->string('name_with_initials')->nullable();
            $table->string('yearbook_name')->nullable();
            $table->string('nic_number')->unique()->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->longText('photo')->nullable();

            // Current Membership
            $table->enum('membership_type', ['Student', 'Graduate', 'Associate', 'Fellow', 'Honorary Fellow'])->nullable();
            $table->string('membership_number')->unique()->nullable();
            $table->string('membership_year')->nullable();
            $table->string('arb_number')->unique()->nullable();

            // Qualifications
            $table->text('academic_qualifications')->nullable();
            $table->text('professional_qualifications')->nullable();

            // Practice/Work Details
            $table->string('practice_name')->nullable();
            $table->text('practice_description')->nullable();
            $table->string('practice_type')->nullable();
            $table->string('location_district')->nullable();
            $table->string('location_province')->nullable();
            $table->string('personal_website')->nullable();

            // Office Contact
            $table->text('office_address')->nullable();
            $table->string('office_district')->nullable();
            $table->string('office_province')->nullable();
            $table->string('office_phone')->nullable();
            $table->string('office_fax')->nullable();
            $table->string('office_email')->nullable();
            $table->string('office_website')->nullable();

            // Home Contact
            $table->text('home_address')->nullable();
            $table->string('home_district')->nullable();
            $table->string('home_province')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('home_fax')->nullable();
            $table->string('home_email')->nullable();

            // Additional Contact Info 1
            $table->text('contact_info_1_address')->nullable();
            $table->string('contact_info_1_phone')->nullable();
            $table->string('contact_info_1_email')->nullable();

            // Additional Contact Info 2
            $table->text('contact_info_2_address')->nullable();
            $table->string('contact_info_2_phone')->nullable();
            $table->string('contact_info_2_email')->nullable();

            // Positions & Awards
            $table->text('positions_held')->nullable();
            $table->text('slia_awards')->nullable();
            $table->text('other_awards')->nullable();

            // Credentials
            $table->string('username')->unique()->nullable();
            $table->string('password')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slia_members');
    }
};
