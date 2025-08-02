<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Создает начальные роли, разрешения и пользователя-админа.
     */
    public function up(): void
    {
        // Создаем разрешения
        Permission::firstOrCreate(['name' => 'create projects']);
        Permission::firstOrCreate(['name' => 'edit projects']);
        Permission::firstOrCreate(['name' => 'view prices']);

        // Создаем роли
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'manager']);

        // Назначаем разрешения роли admin
        $admin = Role::findByName('admin');
        $admin->givePermissionTo(['create projects', 'edit projects', 'view prices']);

        // Назначаем разрешения роли manager
        $manager = Role::findByName('manager');
        $manager->givePermissionTo(['create projects']);

        // Создаем пользователя-админа
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('admin');
        $user->subscription()->create(['is_active' => true, 'expires_at' => now()->addYear()]);
    }

    /**
     * Очищает роли, разрешения и пользователя-админа.
     */
    public function down(): void
    {
        User::where('email', 'admin@example.com')->delete();
        Role::whereIn('name', ['admin', 'manager'])->delete();
        Permission::whereIn('name', ['create projects', 'edit projects', 'view prices'])->delete();
    }
};
