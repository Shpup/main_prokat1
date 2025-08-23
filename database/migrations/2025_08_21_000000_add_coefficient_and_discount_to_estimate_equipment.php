<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_equipment', function (Blueprint $table) {
            $table->float('coefficient')->default(1.0)->after('quantity');
            $table->float('discount')->default(0)->after('coefficient');
        });
    }

    public function down(): void
    {
        Schema::table('estimate_equipment', function (Blueprint $table) {
            $table->dropColumn(['coefficient', 'discount']);
        });
    }
};
