<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blocks') && !Schema::hasColumn('blocks', 'name')) {
            Schema::table('blocks', function (Blueprint $table) {
                // Add name as the first content column if missing
                $table->string('name')->after('id');
                // Optional: if there are existing rows, you might want to set a default via app code later
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('blocks') && Schema::hasColumn('blocks', 'name')) {
            Schema::table('blocks', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
