<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBoardsTable extends Migration
{
    public function up()
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('path')->default('#');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default boards
        DB::table('boards')->insert([
            ['name' => 'Board of Management',           'description' => 'Governs and oversees the strategic direction, events, and programs of the Institute.', 'path' => '/management', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Board of Architectural Education', 'description' => 'Supports education, examinations, and member development across architecture schools.', 'path' => '/bae',       'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Professional Affairs Board',      'description' => 'Oversees practice registrations, CPD events, and professional conduct standards.',   'path' => '/pab',       'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Board of Architectural Publications', 'description' => 'Manages SLIA publications, journals, and archive materials for the profession.', 'path' => '/bap',       'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('boards');
    }
}
