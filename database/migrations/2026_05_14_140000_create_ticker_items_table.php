<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticker_items', function (Blueprint $column) {
            $column->id();
            $column->string('text');
            $column->string('link')->nullable();
            $column->boolean('is_active')->default(true);
            $column->integer('sort_order')->default(0);
            $column->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticker_items');
    }
};
