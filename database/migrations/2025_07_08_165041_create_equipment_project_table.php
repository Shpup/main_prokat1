<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentProjectTable extends Migration
{
    public function up()
    {
        Schema::create('equipment_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('на складе');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment_project');
    }
}
