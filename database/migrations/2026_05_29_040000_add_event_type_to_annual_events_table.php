<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('annual_events', function (Blueprint $table) {
            $table->string('event_type', 40)->default('agm')->after('year');
        });
    }

    public function down()
    {
        Schema::table('annual_events', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });
    }
};
