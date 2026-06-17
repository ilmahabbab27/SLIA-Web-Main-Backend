<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAwardSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('award_settings', function (Blueprint $table) {
            $table->id();
            $table->string('year', 20);
            $table->text('general_guideline');
            $table->timestamps();
        });

        DB::table('award_settings')->insert([
            'id' => 1,
            'year' => '2026',
            'general_guideline' => "SLIA's Architectural Awards are the most prominent Awards in Sri Lanka simply because selection is by a renowned panel of Jurors, a Technical Evaluation Committee and an International Juror who is familiar with local Architecture and who recognizes and acknowledges excellence in Architecture by physically visiting all shortlisted projects island-wide is the key feature of this most outstanding award scheme.\n\nThe scrutiny and strict contingent evaluation and anonymous marking process ensure a very contingent selection process to recognize Architecture during the short listing phase. Additionally, the subsequent physical visits to all sites further enhances credentials and credibility to recognize the high standards in Architecture and is the pride of SLIA's legendary Awards Scheme.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('award_settings');
    }
}
