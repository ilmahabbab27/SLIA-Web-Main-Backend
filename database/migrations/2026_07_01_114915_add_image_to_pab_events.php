<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToPabEvents extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pab_events') && !Schema::hasColumn('pab_events', 'image_name')) {
            Schema::table('pab_events', function (Blueprint $table) {
                $table->string('image_name')->nullable()->after('file_src');
                $table->longText('image_src')->nullable()->after('image_name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pab_events') && Schema::hasColumn('pab_events', 'image_name')) {
            Schema::table('pab_events', function (Blueprint $table) {
                $table->dropColumn(['image_name', 'image_src']);
            });
        }
    }
}
