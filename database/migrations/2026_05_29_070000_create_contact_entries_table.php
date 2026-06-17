<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contact_entries', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('label');
            $table->string('icon', 40);
            $table->string('text', 1000);
            $table->string('href', 1000)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_entries');
    }
};