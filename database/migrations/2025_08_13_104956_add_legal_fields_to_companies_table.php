<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('registration_country')->nullable();
            $table->string('inn', 20)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('short_name', 255)->nullable();
            $table->string('legal_address', 255)->nullable();
            $table->string('postal_address', 255)->nullable();
            $table->string('kpp', 20)->nullable();
            $table->string('ogrn', 20)->nullable();
            $table->string('okpo', 20)->nullable();
            $table->string('bik', 20)->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->string('correspondent_account', 20)->nullable();
            $table->string('checking_account', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('registration_address', 255)->nullable();
            $table->string('ogrnip', 20)->nullable();
            $table->string('certificate_number', 20)->nullable();
            $table->date('certificate_date')->nullable();
            $table->string('card_number', 20)->nullable();
            $table->string('snils', 20)->nullable();
            $table->string('passport_data', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'registration_country', 'inn', 'full_name', 'short_name', 'legal_address',
                'postal_address', 'kpp', 'ogrn', 'okpo', 'bik', 'bank_name',
                'correspondent_account', 'checking_account', 'phone', 'email',
                'registration_address', 'ogrnip', 'certificate_number', 'certificate_date',
                'card_number', 'snils', 'passport_data'
            ]);
        });
    }
};
