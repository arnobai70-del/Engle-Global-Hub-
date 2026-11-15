<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('service', 40);
            // Set when a signed-in customer submits, so it appears in their portal.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('destination_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('route_or_destination', 200)->nullable();
            $table->date('travel_date')->nullable();
            $table->date('return_date')->nullable();
            $table->unsignedTinyInteger('travellers')->default(1);
            $table->string('full_name', 150);
            $table->string('email', 254);
            $table->string('phone', 32);
            $table->text('message')->nullable();
            $table->dateTime('consented_at');
            $table->string('status', 30)->default('new');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index(['service', 'status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_enquiries');
    }
};
