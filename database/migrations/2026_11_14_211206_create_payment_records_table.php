<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->morphs('payable');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->string('method', 30);
            $table->date('paid_on');
            // Bank / mobile-banking transaction ID, used to stop the same payment being recorded twice.
            $table->string('payer_reference', 100)->nullable();
            $table->string('purpose', 200);
            $table->string('receipt_path', 255)->nullable();
            $table->string('receipt_mime', 100)->nullable();
            $table->string('status', 30)->default('pending_verification');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_note', 1000)->nullable();
            $table->timestamps();
            $table->unique(['method', 'payer_reference']);
            $table->index(['status', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
