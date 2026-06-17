<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateBapDefaultIntroduction extends Migration
{
    public function up()
    {
        $introduction = "The Board of Architectural Publications (BAP) provides assistance, advice, control and manages the printing and publications for the Sri Lanka Institute of Architects in printed, electronic or digital format.\n\nThe BAP consists of three Standing Committees and six working committees.\n\nStanding Committees\n1. Printing and Publications Committee\n2. Graphics Committee\n3. Library Committee\n\nWorking Committees\n1. Media Committee\n2. The Architect magazine editorial board\n3. Vastu magazine editorial board\n4. Built environmental journal editorial board\n5. Archive journal editorial board\n6. Art Circle - Ad Hoc committee";

        $exists = DB::table('bap_settings')->where('id', 1)->exists();

        if ($exists) {
            DB::table('bap_settings')->where('id', 1)->update([
                'introduction' => $introduction,
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('bap_settings')->insert([
            'id' => 1,
            'introduction' => $introduction,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('bap_settings')->where('id', 1)->update([
            'introduction' => 'Browse SLIA publications and archives. Use the Publications tab to download issues and reference material.',
            'updated_at' => now(),
        ]);
    }
}
