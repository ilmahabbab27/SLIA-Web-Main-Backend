<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->string('student_membership_year', 20)->nullable()->after('student_membership_number');
            $table->string('graduate_membership_year', 20)->nullable()->after('graduate_membership_number');
            $table->string('associate_membership_year', 20)->nullable()->after('associate_membership_number');
        });
    }

    public function down()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->dropColumn([
                'student_membership_year',
                'graduate_membership_year',
                'associate_membership_year',
            ]);
        });
    }
};
