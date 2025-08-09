
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminIdToSitesTable extends Migration
{
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->after('id')
                ->constrained('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });
    }
}
