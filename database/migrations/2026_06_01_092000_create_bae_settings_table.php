<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bae_settings', function (Blueprint $table) {
            $table->id();
            $table->text('introduction');
            $table->timestamps();
        });

        DB::table('bae_settings')->insert([
            'id' => 1,
            'introduction' => "The Board of Architectural Education supports the Institute's education and examination related work, including application processes, notices, reference material, and member resources.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('bae_settings');
    }
};
