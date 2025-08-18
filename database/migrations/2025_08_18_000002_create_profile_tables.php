<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_contacts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['phone','email']);
            $t->string('value', 191)->nullable();
            $t->string('comment', 255)->nullable();
            $t->boolean('is_primary')->default(false);
            $t->timestamps();
            $t->index(['user_id','type']);
        });

        Schema::create('user_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['passport','foreign_passport','driver_license']);
            $t->string('series', 32)->nullable();
            $t->string('number', 64)->nullable();
            $t->date('issued_at')->nullable();
            $t->string('issued_by', 255)->nullable();
            $t->date('expires_at')->nullable();
            $t->string('comment', 255)->nullable();
            $t->timestamps();
            $t->index(['user_id','type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_documents');
        Schema::dropIfExists('user_contacts');
    }
};


