<?php

namespace App\Console\Commands;

use App\Models\HomepageBlock;
use App\Services\HomepageContentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('homepage:import-default-blocks {--apply : Insert missing defaults; without this flag only preview changes}')]
#[Description('Preview or safely import current public homepage defaults as editable blocks, without overwriting existing records')]
final class ImportHomepageDefaultBlocks extends Command
{
    public function handle(HomepageContentService $content): int
    {
        $defaults = $content->defaultEditableBlocks();
        $pending = [];

        foreach ($defaults as $row) {
            if (! HomepageBlock::query()->where('section', $row['section'])->where('key', $row['key'])->exists()) {
                $pending[] = $row;
            }
        }

        $this->info('Default blocks: '.count($defaults));
        $this->info('Missing blocks: '.count($pending));
        $this->info('Existing blocks left untouched: '.(count($defaults) - count($pending)));

        if (! $this->option('apply')) {
            $this->warn('DRY RUN — no data was changed. Pass --apply after making and verifying a database backup.');

            return self::SUCCESS;
        }

        $inserted = DB::transaction(function () use ($pending): int {
            $count = 0;
            foreach ($pending as $row) {
                $exists = HomepageBlock::query()->where('section', $row['section'])
                    ->where('key', $row['key'])->exists();
                if ($exists) {
                    continue;
                }
                HomepageBlock::query()->create($row);
                $count++;
            }

            return $count;
        });

        $this->info("Imported {$inserted} missing blocks. Existing records were not modified.");

        return self::SUCCESS;
    }
}
