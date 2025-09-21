<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bring existing `blocks` table (if present) up-to-date with nesting/ordering columns.
     */
    public function up(): void
    {
        if (!Schema::hasTable('blocks')) {
            return; // fresh installs will use the create migration
        }

        Schema::table('blocks', function (Blueprint $table) {
            // Add columns only if they are missing
            if (!Schema::hasColumn('blocks', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('blocks', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('blocks')->nullOnDelete();
            }
            if (!Schema::hasColumn('blocks', 'position')) {
                $table->unsignedInteger('position')->default(0);
            }
            if (!Schema::hasColumn('blocks', 'year')) {
                $table->smallInteger('year')->nullable();
            }
            if (!Schema::hasColumn('blocks', 'level')) {
                $table->string('level', 20)->nullable();
            }
            if (!Schema::hasColumn('blocks', 'visible')) {
                $table->boolean('visible')->default(true);
            }
        });
    }

    public function down(): void
    {
        // No-op: we won't try to guess which columns existed before.
        // If needed, rollbacks can be handled manually.
    }
};
