<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->enum('current_membership_type', ['student', 'graduate', 'associate'])->default('student')->after('member_type')->comment('Currently active membership type');
        });
    }

    public function down()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->dropColumn('current_membership_type');
        });
    }
};
