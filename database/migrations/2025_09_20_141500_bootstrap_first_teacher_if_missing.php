<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role')) {
            return;
        }

        // If there is no teacher yet, promote the earliest user to teacher
        try {
            $hasTeacher = DB::table('users')->where('role', 'teacher')->exists();
            if (!$hasTeacher) {
                $first = DB::table('users')->orderBy('id', 'asc')->first();
                if ($first) {
                    DB::table('users')->where('id', $first->id)->update(['role' => 'teacher']);
                }
            }
        } catch (\Throwable $e) {
            // no-op: avoid failing deployments if table structure is unexpected
        }
    }

    public function down(): void
    {
        // No-op: we do not downgrade roles automatically
    }
};
