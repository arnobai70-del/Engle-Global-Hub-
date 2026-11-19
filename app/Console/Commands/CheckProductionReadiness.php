<?php

namespace App\Console\Commands;

use App\Services\System\ProductionReadiness;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:production-readiness')]
#[Description('Verify production readiness without contacting providers or changing state')]
final class CheckProductionReadiness extends Command
{
    public function handle(ProductionReadiness $readiness): int
    {
        $checks = $readiness->checks();

        $this->newLine();
        $this->info('Production readiness checks');

        $hasFailures = false;

        foreach ($checks as $check) {
            $line = "[{$check['status']}] {$check['label']}";

            match ($check['status']) {
                ProductionReadiness::FAIL => $this->error($line),
                ProductionReadiness::WARN => $this->warn($line),
                default => $this->line($line),
            };

            $hasFailures = $hasFailures || $check['status'] === ProductionReadiness::FAIL;
        }

        $this->newLine();

        if ($hasFailures) {
            $this->error('Production readiness verification failed. No state was changed.');

            return self::FAILURE;
        }

        $this->info('Production readiness verification passed. No state was changed.');

        return self::SUCCESS;
    }
}
