<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBomItemsTable extends Migration
{
    public function up()
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 40);
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('image')->nullable();
            $table->text('link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bom_items');
    }
}
