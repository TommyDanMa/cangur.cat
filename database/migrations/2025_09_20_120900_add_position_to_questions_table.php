<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('questions') && !Schema::hasColumn('questions', 'position')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->unsignedInteger('position')->default(0)->after('block_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('questions') && Schema::hasColumn('questions', 'position')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }
};
