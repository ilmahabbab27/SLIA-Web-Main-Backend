<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBomSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('bom_settings', function (Blueprint $table) {
            $table->id();
            $table->string('introduction_title');
            $table->text('introduction');
            $table->json('focus_points')->nullable();
            $table->timestamps();
        });

        DB::table('bom_settings')->insert([
            'id' => 1,
            'introduction_title' => 'Board of Management',
            'introduction' => 'The Board of Management oversees strategic direction, programs, and professional associations.',
            'focus_points' => json_encode([
                'Governance and institutional strategy',
                'Professional development programs',
                'Collaboration with associations',
                'Operational oversight and planning',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('bom_settings');
    }
}
