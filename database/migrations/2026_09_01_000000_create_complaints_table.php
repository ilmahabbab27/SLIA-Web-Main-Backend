<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('category');
            $table->string('subject');
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('category');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
};
