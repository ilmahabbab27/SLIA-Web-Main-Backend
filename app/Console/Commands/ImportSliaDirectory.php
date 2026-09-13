<?php

namespace App\Console\Commands;

use App\Http\Controllers\SliaMemberController;
use App\Models\SliaMember;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportSliaDirectory extends Command
{
    protected $signature = 'slia:import-directory {path} {--rebuild : Re-copy the original member table before importing}';
    protected $description = 'Import the SLIA member directory workbook into the active directory table';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("Workbook not found: {$path}");
            return self::FAILURE;
        }

        if ($this->option('rebuild')) {
            SliaMember::query()->delete();
            foreach (DB::table('slia_members')->get() as $member) {
                $data = (array) $member;
                unset($data['id']);
                $data['source_data'] = json_encode($data, JSON_UNESCAPED_UNICODE);
                SliaMember::create($data);
            }
        }

        $rows = (new SliaMemberController())->readXlsx($path);
        $imported = 0;

        foreach ($rows as $row) {
            if (empty($row['full_name'])) {
                continue;
            }
            if (!empty($row['password'])) {
                $row['password'] = Hash::make((string) $row['password']);
            }

            if (!empty($row['membership_number'])) {
                SliaMember::updateOrCreate(['membership_number' => $row['membership_number']], $row);
            } else {
                SliaMember::create($row);
            }
            $imported++;
        }

        $this->info("Imported {$imported} member records.");
        return self::SUCCESS;
    }
}
