<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->string('current_membership_status', 50)->default('active')->after('is_active')->comment('active, inactive, expired, suspended, cancelled');
        });
    }

    public function down()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->dropColumn('current_membership_status');
        });
    }
};
