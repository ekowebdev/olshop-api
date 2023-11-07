<?php

namespace Database\Seeders;

use App\Http\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::create(['name' => 'edit users', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete users', 'guard_name' => 'api']);
        Permission::create(['name' => 'create users', 'guard_name' => 'api']);
        Permission::create(['name' => 'view users', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit item gifts', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete item gifts', 'guard_name' => 'api']);
        Permission::create(['name' => 'create item gifts', 'guard_name' => 'api']);
        Permission::create(['name' => 'view item gifts', 'guard_name' => 'api']);
        $role_admin = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $role_admin->givePermissionTo(Permission::all());
        $role_customer = Role::create(['name' => 'customer', 'guard_name' => 'api']);
        $role_customer->givePermissionTo(['view item gifts']);
        $user_admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@bakti.shop',
            'email_verified_at' => now(),
            'password' => Hash::make('123456'),
        ]);
        $user_admin->assignRole('admin');
    }
}
