<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_intervals', function (Blueprint $table) {
            $table->float('coefficient')->default(1.0)->nullable();
            $table->float('discount')->default(0.0)->nullable();
        });
    }

    public function down(): void
    {

    }
};
