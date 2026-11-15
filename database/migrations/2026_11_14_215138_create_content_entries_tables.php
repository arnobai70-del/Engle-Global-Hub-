<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 40);
            $table->string('slug', 160);
            $table->string('title', 200);
            $table->string('summary', 500)->nullable();
            $table->text('body')->nullable();
            $table->string('title_bn', 200)->nullable();
            $table->string('summary_bn', 500)->nullable();
            $table->text('body_bn')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('location', 200)->nullable();
            $table->string('contact_phone', 32)->nullable();
            $table->string('contact_email', 254)->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->json('source_urls')->nullable();
            $table->string('consent_reference', 200)->nullable();
            $table->date('consent_recorded_at')->nullable();
            $table->json('related_destination_ids')->nullable();
            $table->json('related_entry_ids')->nullable();
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 170)->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('owner_approved_at')->nullable();
            $table->foreignId('owner_approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['type', 'slug']);
            $table->index(['type', 'status', 'sort_order']);
        });

        Schema::create('content_entry_media', function (Blueprint $table): void {
            $table->foreignId('content_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['content_entry_id', 'media_asset_id']);
        });

        Schema::create('content_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20);
            $table->json('snapshot');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('content_entry_media');
        Schema::dropIfExists('content_entries');
    }
};
