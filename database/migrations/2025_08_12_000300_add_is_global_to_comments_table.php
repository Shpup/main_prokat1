<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('comment');
                $table->index(['project_id', 'is_global']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'is_global')) {
                $table->dropIndex(['project_id', 'is_global']);
                $table->dropColumn('is_global');
            }
        });
    }
};


