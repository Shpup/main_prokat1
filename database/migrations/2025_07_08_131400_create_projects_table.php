<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создает таблицу для хранения проектов.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название проекта
            $table->text('description')->nullable(); // Описание проекта
            $table->foreignId('manager_id')->constrained('users')->onDelete('set null'); // Менеджер проекта
            $table->date('start_date'); // Дата начала
            $table->date('end_date')->nullable(); // Дата окончания
            $table->timestamps();
        });
    }

    /**
     * Удаляет таблицу проектов.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
