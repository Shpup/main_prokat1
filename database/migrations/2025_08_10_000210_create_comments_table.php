<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('comment');
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['project_id', 'date']);
            $table->index(['employee_id', 'start_time', 'end_time']);
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};


