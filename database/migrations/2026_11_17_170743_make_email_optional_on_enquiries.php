<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website leads may give only a phone or WhatsApp number. The email column keeps
 * its type; only NULL becomes allowed. Existing rows are not touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['student_enquiries', 'service_enquiries'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('email', 254)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['student_enquiries', 'service_enquiries'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('email', 254)->nullable(false)->change();
            });
        }
    }
};
