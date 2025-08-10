<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_intervals') && Schema::hasColumn('work_intervals', 'comment')) {
            Schema::table('work_intervals', function (Blueprint $table) {
                $table->dropColumn('comment');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_intervals') && !Schema::hasColumn('work_intervals', 'comment')) {
            Schema::table('work_intervals', function (Blueprint $table) {
                $table->text('comment')->nullable();
            });
        }
    }
};


