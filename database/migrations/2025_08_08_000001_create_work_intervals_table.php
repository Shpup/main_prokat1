<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // btree_gist необходим для EXCLUDE
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        Schema::create('work_intervals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('type', 8); // work|busy|off
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // Добавляем сгенерированную колонку tsrange ts (без таймзоны)
        DB::statement(<<<SQL
            ALTER TABLE work_intervals
            ADD COLUMN ts tsrange GENERATED ALWAYS AS (
              tsrange(
                (date::timestamp) + start_time,
                (date::timestamp) + end_time,
                '[)'
              )
            ) STORED
        SQL);

        // Индексы
        DB::statement('CREATE INDEX work_intervals_gist_employee_ts ON work_intervals USING GIST (employee_id, ts)');
        DB::statement('CREATE INDEX work_intervals_employee_date_idx ON work_intervals (employee_id, date)');

        // Ограничение на пересечения интервалов одного сотрудника
        DB::statement(<<<SQL
            ALTER TABLE work_intervals
            ADD CONSTRAINT no_overlap_per_employee
            EXCLUDE USING GIST (employee_id WITH =, ts WITH &&)
            DEFERRABLE INITIALLY DEFERRED
        SQL);
    }

    public function down(): void
    {
        // Сначала снимаем ограничение и индексы
        DB::statement('ALTER TABLE IF EXISTS work_intervals DROP CONSTRAINT IF EXISTS no_overlap_per_employee');
        DB::statement('DROP INDEX IF EXISTS work_intervals_gist_employee_ts');
        DB::statement('DROP INDEX IF EXISTS work_intervals_employee_date_idx');
        Schema::dropIfExists('work_intervals');
    }
};


