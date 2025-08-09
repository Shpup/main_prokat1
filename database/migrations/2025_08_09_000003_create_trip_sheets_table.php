<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripSheetsTable extends Migration
{
    public function up()
    {
        Schema::create('trip_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('date_time');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained('sites')->onDelete('set null');
            $table->string('address')->nullable();
            $table->decimal('distance', 8, 2)->nullable(); // Расстояние в км
            $table->decimal('cost', 8, 2)->nullable(); // Стоимость в RUB
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_sheets');
    }
}
