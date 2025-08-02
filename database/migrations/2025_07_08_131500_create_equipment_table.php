<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создает таблицу для хранения оборудования.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название оборудования
            $table->text('description')->nullable(); // Описание
            $table->decimal('price', 8, 2)->nullable(); // Цена
            $table->json('specifications')->nullable(); // Характеристики (JSON)
            $table->string('barcode')->unique(); // Штрихкод
            $table->string('image')->nullable(); // Путь к изображению
            $table->timestamps();
        });

        // Промежуточная таблица для связи проектов и оборудования
        Schema::create('project_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('on_stock'); // Статус: on_stock, assigned, etc.
            $table->timestamps();
        });
    }

    /**
     * Удаляет таблицы оборудования и связи.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_equipment');
        Schema::dropIfExists('equipment');
    }
};
