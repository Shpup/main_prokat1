<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_intervals', function (Blueprint $table) {
            if (Schema::hasColumn('work_intervals', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_intervals', function (Blueprint $table) {
            if (!Schema::hasColumn('work_intervals', 'phone')) {
                $table->string('phone', 32)->nullable()->after('comment');
            }
        });
    }
};


