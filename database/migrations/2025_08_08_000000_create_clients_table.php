<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->decimal('discount_equipment', 5, 2)->default(0.00);
            $table->decimal('discount_services', 5, 2)->default(0.00);
            $table->decimal('discount_materials', 5, 2)->default(0.00);
            $table->boolean('blacklisted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
