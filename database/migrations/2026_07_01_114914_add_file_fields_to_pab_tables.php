<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileFieldsToPabTables extends Migration
{
    public function up()
    {
        // Add file fields to pab_events if they don't exist
        if (Schema::hasTable('pab_events') && !Schema::hasColumn('pab_events', 'file_name')) {
            Schema::table('pab_events', function (Blueprint $table) {
                $table->string('file_name')->nullable()->after('link');
                $table->longText('file_src')->nullable()->after('file_name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pab_events') && Schema::hasColumn('pab_events', 'file_name')) {
            Schema::table('pab_events', function (Blueprint $table) {
                $table->dropColumn(['file_name', 'file_src']);
            });
        }
    }
}
