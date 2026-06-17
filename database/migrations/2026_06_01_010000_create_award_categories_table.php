<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAwardCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('award_categories', function (Blueprint $table) {
            $table->id();
            $table->string('year', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('guideline')->nullable();
            $table->text('general_guideline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['year', 'is_active']);
        });

        $generalGuideline = "SLIA's Architectural Awards are the most prominent Awards in Sri Lanka simply because selection is by a renowned panel of Jurors, a Technical Evaluation Committee and an International Juror who is familiar with local Architecture and who recognizes and acknowledges excellence in Architecture by physically visiting all shortlisted projects island-wide is the key feature of this most outstanding award scheme.\n\nThe scrutiny and strict contingent evaluation and anonymous marking process ensure a very contingent selection process to recognize Architecture during the short listing phase. Additionally, the subsequent physical visits to all sites further enhances credentials and credibility to recognize the high standards in Architecture and is the pride of SLIA's legendary Awards Scheme.";

        DB::table('award_categories')->insert([
            [
                'year' => '2026',
                'name' => 'Design Awards',
                'description' => 'Recognizing and rewarding built architectural works that address the needs and aspirations of excellence in Sri Lankan design.',
                'guideline' => 'Submit completed built work with project documentation, drawings, photographs, and consent for jury site visits where shortlisted.',
                'general_guideline' => $generalGuideline,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2026',
                'name' => 'Architectural Heritage Conservation',
                'description' => 'Recognizing projects that conserve, restore, and celebrate Sri Lanka\'s architectural heritage.',
                'guideline' => 'Include conservation approach, historical research, intervention details, and evidence of completed or implemented work.',
                'general_guideline' => $generalGuideline,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2026',
                'name' => 'Green & Sustainable Architecture',
                'description' => 'Honoring sustainable design approaches that respond to climate, context, and long-term resilience.',
                'guideline' => 'Provide sustainability objectives, measured or expected environmental performance, and supporting design documentation.',
                'general_guideline' => $generalGuideline,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('award_categories');
    }
}
