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
        Permission::create(['name' => 'edit products', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete products', 'guard_name' => 'api']);
        Permission::create(['name' => 'create products', 'guard_name' => 'api']);
        Permission::create(['name' => 'view products', 'guard_name' => 'api']);
        $role_admin = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $role_admin->givePermissionTo(Permission::all());
        $role_customer = Role::create(['name' => 'customer', 'guard_name' => 'api']);
        $role_customer->givePermissionTo(['view products']);
        $user_admin = User::create([
            'username' => 'admin',
            'email' => 'baktiwebid@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
        ]);
        $user_admin->assignRole('admin');
        $user_admin->profile()->create(['name' => 'Administrator', 'birthdate' => '1990-01-01']);
    }
}
