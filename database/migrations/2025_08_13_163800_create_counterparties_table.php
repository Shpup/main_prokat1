<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCounterpartiesTable extends Migration
{
    public function up()
    {
        Schema::create('counterparties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->nullable();
            $table->enum('status', ['new', 'verified', 'dangerous'])->nullable();
            $table->string('actual_address')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_available_for_sublease')->default(false);
            $table->string('type')->nullable()->default('ur');
            $table->string('registration_country')->nullable();
            $table->string('inn')->nullable();
            $table->string('full_name')->nullable();
            $table->string('short_name')->nullable();
            $table->string('legal_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('kpp')->nullable();
            $table->string('ogrn')->nullable();
            $table->string('okpo')->nullable();
            $table->string('bik')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('correspondent_account')->nullable();
            $table->string('checking_account')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('registration_address')->nullable();
            $table->string('ogrnip')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('certificate_date')->nullable();
            $table->string('card_number')->nullable();
            $table->string('snils')->nullable();
            $table->string('passport_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('counterparties');
    }
}
