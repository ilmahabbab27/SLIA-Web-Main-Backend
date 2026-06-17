<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('board_members', function (Blueprint $table) {
            $table->id();
            $table->string('board_key', 40);
            $table->longText('image')->nullable();
            $table->string('name');
            $table->string('designation');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['board_key', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('board_members');
    }
};