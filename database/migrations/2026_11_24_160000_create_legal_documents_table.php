<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->string('document_type', 50);
            $table->string('issuer', 180);
            $table->string('reference_number', 120)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->date('verified_at')->nullable();
            $table->string('verification_status', 30)->default('pending');
            $table->string('publication_status', 30)->default('draft');
            $table->string('private_path', 500);
            $table->string('private_sha256', 64);
            $table->string('public_copy_path', 500)->nullable();
            $table->string('public_copy_sha256', 64)->nullable();
            $table->boolean('redaction_confirmed')->default(false);
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['publication_status', 'verification_status', 'expires_at'], 'legal_documents_public_lookup');
            $table->unique('private_sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
