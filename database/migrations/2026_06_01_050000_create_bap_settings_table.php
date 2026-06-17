<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBapSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('bap_settings', function (Blueprint $table) {
            $table->id();
            $table->text('introduction');
            $table->timestamps();
        });

        DB::table('bap_settings')->insert([
            'id' => 1,
            'introduction' => 'Browse SLIA publications and archives. Use the Publications tab to download issues and reference material.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('bap_settings');
    }
}
