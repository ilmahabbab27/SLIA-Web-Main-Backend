<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->string('name_with_initials')->nullable()->after('name');
            $table->string('gender', 20)->nullable()->after('name_with_initials');
            $table->longText('picture')->nullable()->after('gender');
            $table->string('nic', 50)->nullable()->after('picture');
            $table->string('nationality', 100)->nullable()->after('nic');
            $table->date('date_of_birth')->nullable()->after('nationality');
            $table->text('office_address')->nullable()->after('address');
            $table->string('office_phone', 80)->nullable()->after('office_address');
            $table->string('office_email', 255)->nullable()->after('office_phone');
            $table->text('residence_address')->nullable()->after('office_email');
            $table->string('residence_phone', 80)->nullable()->after('residence_address');
            $table->string('residence_email', 255)->nullable()->after('residence_phone');
        });
    }

    public function down()
    {
        Schema::table('bae_members', function (Blueprint $table) {
            $table->dropColumn([
                'name_with_initials',
                'gender',
                'picture',
                'nic',
                'nationality',
                'date_of_birth',
                'office_address',
                'office_phone',
                'office_email',
                'residence_address',
                'residence_phone',
                'residence_email',
            ]);
        });
    }
};
