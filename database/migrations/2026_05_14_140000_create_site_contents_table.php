<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteContentsTable extends Migration
{
    public function up()
    {
        Schema::create('site_contents', function (Blueprint $row) {
            $row->id();
            $row->string('key')->unique(); // e.g., 'about_description', 'history_text'
            $row->string('title')->nullable();
            $row->text('content')->nullable();
            $row->string('image')->nullable();
            $row->string('link')->nullable();
            $row->boolean('is_active')->default(true);
            $row->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_contents');
    }
}
