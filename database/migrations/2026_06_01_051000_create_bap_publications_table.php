<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBapPublicationsTable extends Migration
{
    public function up()
    {
        Schema::create('bap_publications', function (Blueprint $table) {
            $table->id();
            $table->string('category', 80);
            $table->string('title');
            $table->string('volume')->nullable();
            $table->date('uploaded_at')->nullable();
            $table->longText('cover_src')->nullable();
            $table->string('file_name')->nullable();
            $table->longText('file_src')->nullable();
            $table->text('external_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bap_publications');
    }
}
