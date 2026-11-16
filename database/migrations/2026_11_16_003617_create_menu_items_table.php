<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-managed navigation: header and footer menus, homepage service tiles and the header button.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->string('location', 20);
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('key', 80)->nullable();
            $table->string('label_en', 80);
            $table->string('label_bn', 80)->nullable();
            $table->string('target', 300)->nullable();
            $table->string('fragment', 60)->nullable();
            $table->string('icon', 30)->nullable();
            $table->string('feature', 40)->nullable();
            $table->string('visibility', 16)->default('all');
            $table->json('roles')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['location', 'key']);
            $table->index(['location', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
