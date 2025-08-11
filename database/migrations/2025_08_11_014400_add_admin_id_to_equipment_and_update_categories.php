<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем admin_id в таблицу equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->bigInteger('admin_id')->unsigned()->nullable()->after('id');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Если user_id в categories уже используется как admin_id, ничего не меняем
        // Если нужно переименовать user_id на admin_id:
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('user_id', 'admin_id');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('admin_id', 'user_id');
        });
    }
};
