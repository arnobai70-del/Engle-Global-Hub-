<?php

namespace Tests\Feature;

use Tests\TestCase;

class MigrationPortabilityTest extends TestCase
{
    /**
     * With MariaDB's default explicit_defaults_for_timestamp=OFF, a NOT NULL TIMESTAMP
     * column without a default either gets ON UPDATE CURRENT_TIMESTAMP (first one in the
     * table, silently overwriting the value on every update) or fails with "Invalid
     * default value". SQLite accepts both, so the problem only appears in production.
     */
    public function test_no_migration_declares_a_required_timestamp_without_a_default(): void
    {
        $offending = [];

        foreach (glob(database_path('migrations/*.php')) as $path) {
            foreach (file($path) as $number => $line) {
                if (preg_match("/->timestamp(Tz)?\('[a-z_]+'/", $line) && ! preg_match('/nullable|useCurrent|default\(/', $line)) {
                    $offending[] = basename($path).':'.($number + 1);
                }
            }
        }

        $this->assertSame([], $offending, 'Use dateTime() for required timestamps, or make them nullable.');
    }
}
