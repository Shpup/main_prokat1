<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('Смета 1'); // название сметы
            $table->foreignId('client_id')->nullable()->constrained('clients');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // Pivot для equipment в estimate
        Schema::create('estimate_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('on_stock'); // on_stock, assigned, used
            $table->integer('quantity')->default(1); // новое, для кол-ва
            $table->timestamps();
        });

        // Staff в estimate (many-to-many, если нужно separate staff per estimate; иначе используем project->staff)
        // Для простоты: staff от проекта, но если нужно separate — добавь pivot estimate_user аналогично project_user
    }
};
