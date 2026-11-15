<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 40);
            $table->string('event_id', 191);
            $table->string('event_type', 100)->nullable();
            $table->char('payload_hash', 64);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            // Idempotency: each provider event is handled once.
            $table->unique(['provider', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
