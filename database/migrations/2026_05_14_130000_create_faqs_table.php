<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFaqsTable extends Migration
{
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default FAQs
        DB::table('faqs')->insert([
            [
                'question' => 'WHAT ARE THE SLIA & THE ARB',
                'answer' => 'Sri Lanka Institute of Architects (SLIA) is an organization incorporated by an Act of Parliament. Architects Registration Board (ARB) registers qualified persons under three categories namely Chartered Architects, Architects and Architectural Licentiates.',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'question' => 'WHO DRAW PLANS',
                'answer' => 'Only qualified Architects design meaningful, functional and aesthetic spaces. You should check if they are registered with the ARB and members of the SLIA.',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'question' => 'How can I select my Architect',
                'answer' => 'You may select your Architect by visiting www.slia.lk, emailing secretariat@architects.lk, calling 011 2697109, or referring to the Architects Directory.',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('faqs');
    }
}
