<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaeDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('bae_documents', function (Blueprint $table) {
            $table->id();
            $table->string('category', 40);
            $table->string('title');
            $table->string('meta', 80)->nullable();
            $table->string('file_name')->nullable();
            $table->text('file_src')->nullable();
            $table->text('external_url')->nullable();
            $table->date('uploaded_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bae_documents');
    }
}
