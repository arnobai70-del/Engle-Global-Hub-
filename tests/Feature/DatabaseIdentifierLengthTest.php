<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIdentifierLengthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * MySQL and MariaDB reject identifiers longer than 64 characters, while SQLite
     * accepts them, so long names pass locally and only fail on production.
     */
    private const MYSQL_IDENTIFIER_LIMIT = 64;

    public function test_every_index_name_fits_the_mysql_identifier_limit(): void
    {
        $tooLong = [];

        foreach (Schema::getTableListing(schemaQualified: false) as $table) {
            foreach (Schema::getIndexes($table) as $index) {
                if (strlen($index['name']) > self::MYSQL_IDENTIFIER_LIMIT) {
                    $tooLong[] = $table.'.'.$index['name'];
                }
            }
        }

        $this->assertSame([], $tooLong);
    }
}
