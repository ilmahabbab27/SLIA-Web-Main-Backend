<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSectionFlagsToEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('show_latest')->default(false)->after('category');
            $table->boolean('show_upcoming')->default(false)->after('show_latest');
            $table->boolean('show_past')->default(false)->after('show_upcoming');
            $table->boolean('show_program')->default(false)->after('show_past');
            $table->boolean('show_highlight')->default(false)->after('show_program');
        });

        DB::table('events')->orderBy('id')->chunk(100, function ($events) {
            foreach ($events as $event) {
                DB::table('events')
                    ->where('id', $event->id)
                    ->update([
                        'show_upcoming' => $event->category === 'upcoming',
                        'show_past' => $event->category === 'past',
                        'show_program' => $event->category === 'program',
                        'show_highlight' => $event->category === 'highlight',
                    ]);
            }
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'show_latest',
                'show_upcoming',
                'show_past',
                'show_program',
                'show_highlight',
            ]);
        });
    }
}
