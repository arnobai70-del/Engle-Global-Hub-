<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Explore Bangladesh destinations a "Plan my trip" enquiry asks about,
 * so staff see them in Admin › Service Enquiries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_service_enquiry', function (Blueprint $table): void {
            $table->foreignId('service_enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_enquiry_id', 'destination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_service_enquiry');
    }
};
