<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Переиспользуем файл для миграции work_intervals: добавляем почасовую и проектную оплату
        if (Schema::hasTable('work_intervals')) {
            Schema::table('work_intervals', function (Blueprint $table) {
                if (!Schema::hasColumn('work_intervals', 'hour_rate')) {
                    $table->decimal('hour_rate', 12, 2)->nullable()->after('type');
                }
                if (!Schema::hasColumn('work_intervals', 'project_rate')) {
                    $table->decimal('project_rate', 12, 2)->nullable()->after('hour_rate');
                }
                if (!Schema::hasColumn('work_intervals', 'summ')) {
                    $table->decimal('summ', 12, 2)->nullable()->after('project_rate');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_intervals')) {
            Schema::table('work_intervals', function (Blueprint $table) {
                if (Schema::hasColumn('work_intervals', 'summ')) {
                    $table->dropColumn('summ');
                }
                if (Schema::hasColumn('work_intervals', 'project_rate')) {
                    $table->dropColumn('project_rate');
                }
                if (Schema::hasColumn('work_intervals', 'hour_rate')) {
                    $table->dropColumn('hour_rate');
                }
            });
        }
    }
};


