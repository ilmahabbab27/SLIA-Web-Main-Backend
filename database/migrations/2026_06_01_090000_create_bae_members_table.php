<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bae_members', function (Blueprint $table) {
            $table->id();
            $table->string('member_type', 20);
            $table->unsignedInteger('serial_no')->nullable();
            $table->string('name');
            $table->string('academic_qualifications')->nullable();
            $table->string('membership_year', 20)->nullable();
            $table->string('membership_number', 80)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_no', 80)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['member_type', 'is_active', 'sort_order']);
            $table->index('membership_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bae_members');
    }
};
