<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions
        $permissions = [
            ['name' => 'manage_users', 'description' => 'Can manage users'],
            ['name' => 'create_posts', 'description' => 'Can create posts'],
            ['name' => 'edit_posts', 'description' => 'Can edit posts'],
            ['name' => 'delete_posts', 'description' => 'Can delete posts'],
            // Dito ka magdagdag ng iba pang permissions sa hinaharap
        ];

        // Create permissions and store them in an associative array
        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[$permission['name']] = Permission::firstOrCreate($permission);
        }

        // Define roles
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Administrator with all permissions',
                'permissions' => ['manage_users', 'create_posts', 'edit_posts', 'delete_posts'],
            ],
            [
                'name' => 'Editor',
                'description' => 'Editor with post management permissions',
                'permissions' => ['create_posts', 'edit_posts', 'delete_posts'],
            ],
            // Dito ka magdagdag ng iba pang roles sa hinaharap
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']], // Key para hanapin
                ['description' => $roleData['description']] // Data kung gagawa ng bago
            );
            
            $permissionIds = collect($roleData['permissions'])->map(fn ($name) => $createdPermissions[$name]->id);
            
            $role->permissions()->sync($permissionIds);
        }
    }
}