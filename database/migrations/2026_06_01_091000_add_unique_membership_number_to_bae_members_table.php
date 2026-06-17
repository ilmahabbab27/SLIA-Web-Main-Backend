<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->unique(['member_type', 'membership_number'], 'bae_members_type_number_unique');
        });
    }

    public function down()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->dropUnique('bae_members_type_number_unique');
        });
    }
};
