<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateToBomItemsTable extends Migration
{
    public function up()
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->date('event_date')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->dropColumn('event_date');
        });
    }
}
