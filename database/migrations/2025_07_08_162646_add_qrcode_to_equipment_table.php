<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrcodeToEquipmentTable extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('qrcode')->nullable()->after('barcode');
            $table->dropColumn('barcode');
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('qrcode');
            $table->dropColumn('qrcode');
        });
    }
}
