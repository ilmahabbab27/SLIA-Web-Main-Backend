<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubtitleAndCategoryToEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->string('category')->default('highlight')->after('link'); // 'highlight' or 'program'
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'category']);
        });
    }
}
