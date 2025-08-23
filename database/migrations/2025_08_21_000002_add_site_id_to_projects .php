<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->bigInteger('site_id')->nullable()->after('client_id')->references('id')->on('sites')->onDelete('set null');
        });
    }

    public function down(): void
    {

    }
};
