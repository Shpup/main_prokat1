<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeCompanyFieldsNullable extends Migration
{
    public function up()
    {
        // Разрешаем NULL в колонках, просто удалив NOT NULL
        DB::statement('ALTER TABLE companies ALTER COLUMN "type" DROP NOT NULL');
        DB::statement('ALTER TABLE companies ALTER COLUMN country DROP NOT NULL');
        DB::statement('ALTER TABLE companies ALTER COLUMN tax_rate DROP NOT NULL');
        DB::statement('ALTER TABLE companies ALTER COLUMN accounting_method DROP NOT NULL');
    }

    public function down()
    {
        // Восстанавливаем NOT NULL, если нужно откатить
        DB::statement('ALTER TABLE companies ALTER COLUMN "type" SET NOT NULL');
        DB::statement('ALTER TABLE companies ALTER COLUMN country SET NOT NULL');
        DB::statement('ALTER TABLE companies ALTER COLUMN tax_rate SET NOT NULL');
        DB::statement('ALTER TABLE companies ALTER COLUMN accounting_method SET NOT NULL');
    }
}
