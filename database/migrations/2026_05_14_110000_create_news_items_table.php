<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNewsItemsTable extends Migration
{
    public function up()
    {
        Schema::create('news_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->longText('image')->nullable();
            $table->string('link')->default('#');
            $table->string('type')->default('news'); // 'news' or 'highlight'
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed some initial news data
        DB::table('news_items')->insert([
            [
                'title' => 'Online Revit Course for Architects',
                'subtitle' => 'Eng. Sampath Ekanayake',
                'description' => 'The Sri Lanka Institute of Architects has scheduled the 9th intake of the online Certificate Course in Revit. Contact: Board of Management | Tel. 072 71 64 900',
                'type' => 'news',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Trainer Practices Registration for 2024',
                'subtitle' => 'Register Trainer Practices Annually',
                'description' => 'SLIA Council ratified a BAE recommendation to register Trainer Practices annually. Members interested in registering their Practices as a Trainer Practice are requested to submit applications.',
                'type' => 'news',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Call for Entries for Annual Awards 2023/24',
                'subtitle' => 'Awards Categories',
                'description' => 'Design Award | Colour Award | Young Architect of the Year Award | Research Award | Publication Award | Architectural Conservation Award | Green and Sustainable Architecture Award',
                'type' => 'news',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('news_items');
    }
}
