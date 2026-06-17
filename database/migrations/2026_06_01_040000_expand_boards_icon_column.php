<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandBoardsIconColumn extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE boards MODIFY icon LONGTEXT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE boards MODIFY icon VARCHAR(255) NULL');
    }
}
