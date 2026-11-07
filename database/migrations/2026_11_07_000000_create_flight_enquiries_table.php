<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flight_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 32)->unique();
            $table->string('origin', 3);
            $table->string('destination', 3);
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->string('trip_type', 16);
            $table->unsignedTinyInteger('adults');
            $table->unsignedTinyInteger('children')->default(0);
            $table->unsignedTinyInteger('infants')->default(0);
            $table->string('cabin_class', 32);
            $table->string('contact_name', 150);
            $table->string('contact_email', 254);
            $table->string('contact_phone', 32);
            $table->string('status', 40)->default('new_enquiry');
            $table->decimal('quoted_total_bdt', 12, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_enquiries');
    }
};
