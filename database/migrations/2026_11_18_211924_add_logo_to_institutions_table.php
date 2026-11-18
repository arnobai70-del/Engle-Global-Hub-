<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Partner logo strip: an institution can use a logo from the Media Library.
 * Optional; the logo is shown only for publicly listed institutions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table): void {
            $table->foreignId('logo_media_id')->nullable()->after('website_url')->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('logo_media_id');
        });
    }
};
