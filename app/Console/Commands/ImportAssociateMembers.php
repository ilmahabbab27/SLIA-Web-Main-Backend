<?php

namespace App\Console\Commands;

use App\Http\Controllers\BaeMemberController;
use App\Models\BaeMember;
use Illuminate\Console\Command;

class ImportAssociateMembers extends Command
{
    protected $signature = 'bae:import-associate {path}';
    protected $description = 'Import associate members into the BAE associate member table';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("Workbook not found: {$path}");
            return self::FAILURE;
        }

        $controller = new BaeMemberController();
        $rows = $controller->readWorkbook($path, 'associate');
        $imported = 0;

        foreach ($rows as $row) {
            if (empty($row['name'])) {
                continue;
            }

            foreach (['membership_number', 'associate_membership_number', 'membership_year', 'associate_membership_year'] as $field) {
                if (isset($row[$field]) && trim((string) $row[$field]) === '') {
                    $row[$field] = null;
                }
            }

            $member = BaeMember::updateOrCreate(
                [
                    'member_type' => 'associate',
                    ($row['membership_number'] ? 'membership_number' : 'name') =>
                        $row['membership_number'] ?: $row['name'],
                ],
                $row
            );

            $controller->syncToSliaMember($member->fresh());
            $imported++;
        }

        $this->info("Imported {$imported} associate members.");
        return self::SUCCESS;
    }
}
