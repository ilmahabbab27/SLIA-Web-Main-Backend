<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuidelineAttachmentToAwardCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('award_categories', function (Blueprint $table) {
            $table->string('guideline_attachment_name')->nullable()->after('guideline');
            $table->longText('guideline_attachment')->nullable()->after('guideline_attachment_name');
        });
    }

    public function down()
    {
        Schema::table('award_categories', function (Blueprint $table) {
            $table->dropColumn(['guideline_attachment_name', 'guideline_attachment']);
        });
    }
}
