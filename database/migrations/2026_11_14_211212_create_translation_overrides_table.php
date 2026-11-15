<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_overrides', function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 10);
            $table->char('source_hash', 64);
            $table->text('source');
            $table->text('value');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['locale', 'source_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_overrides');
    }
};
