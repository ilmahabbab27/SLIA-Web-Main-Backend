<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeroButtonsTable extends Migration
{
    public function up()
    {
        Schema::create('hero_buttons', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('link')->default('#');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the three default buttons
        DB::table('hero_buttons')->insert([
            ['label' => 'ANNUAL SUBSCRIPTION',    'link' => '#', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'REGISTER WITH SESSIONS', 'link' => '#', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'APPLY FOR AWARDS',       'link' => '#', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('hero_buttons');
    }
}
