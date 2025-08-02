<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class AddDeleteProjectsPermission extends Migration
{
    public function up()
    {
        Permission::create(['name' => 'delete projects']);
    }

    public function down()
    {
        Permission::where('name', 'delete projects')->delete();
    }
}
