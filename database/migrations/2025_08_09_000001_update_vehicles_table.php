<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('fuel_grade')->nullable()->after('fuel_type');
            $table->decimal('fuel_consumption', 8, 2)->nullable()->after('fuel_grade');
            $table->decimal('diesel_consumption', 8, 2)->nullable()->after('fuel_consumption');
            $table->decimal('battery_capacity', 8, 2)->nullable()->after('diesel_consumption');
            $table->integer('range')->nullable()->after('battery_capacity');
            $table->decimal('hybrid_consumption', 8, 2)->nullable()->after('range');
            $table->integer('hybrid_range')->nullable()->after('hybrid_consumption');
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['fuel_grade', 'fuel_consumption', 'diesel_consumption', 'battery_capacity', 'range', 'hybrid_consumption', 'hybrid_range']);
        });
    }
}
