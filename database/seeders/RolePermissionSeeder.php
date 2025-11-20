<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing role_permissions to start fresh
        // Note: We don't truncate user_roles to preserve existing user-role assignments
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Cleared role-permission associations.');

        // Define all permissions for the application (menus + CRUD actions)
        $resources = [
            'tickets', 'users', 'roles', 'permissions', 'companies', 'customers',
            'employees', 'departments', 'positions', 'helpdeskteams', 'tags', 'reports'
        ];

        $allPermissions = [
            // General / Navigation
            ['name' => 'view_dashboard', 'description' => 'Can view the main dashboard'],
            ['name' => 'view_profile', 'description' => 'Can view and edit own profile'],
            ['name' => 'view_settings', 'description' => 'Can view settings menu'],
        ];

        // Standard CRUD permissions for each resource
        foreach ($resources as $res) {
            $allPermissions[] = ['name' => "view_{$res}", 'description' => "Can view {$res}"];
            $allPermissions[] = ['name' => "create_{$res}", 'description' => "Can create {$res}"];
            $allPermissions[] = ['name' => "edit_{$res}", 'description' => "Can edit {$res}"];
            $allPermissions[] = ['name' => "delete_{$res}", 'description' => "Can delete {$res}"];
        }

        // More specific ticket permissions
        $ticketSpecific = [
            ['name' => 'view_all_tickets', 'description' => 'Can view all tickets'],
            ['name' => 'view_team_tickets', 'description' => 'Can view tickets assigned to their team'],
            ['name' => 'view_my_tickets', 'description' => 'Can view their own assigned tickets'],
            ['name' => 'assign_ticket', 'description' => 'Can assign tickets to users or teams'],
            ['name' => 'change_ticket_status', 'description' => 'Can change ticket status (open/close/etc)'],
            ['name' => 'comment_ticket', 'description' => 'Can add comments to tickets'],
            ['name' => 'export_tickets', 'description' => 'Can export ticket list'],
        ];

        foreach ($ticketSpecific as $p) {
            $allPermissions[] = $p;
        }

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate($permission);
        }

        $this->command->info('All application permissions have been seeded.');

        // Define permission sets for roles (use lowercase role names to avoid DB collation conflicts)
        $adminPermissions = [
            'view_dashboard', 'view_profile', 'view_settings',
            // Users, Roles, Permissions management
            'view_users','create_users','edit_users','delete_users',
            'view_roles','create_roles','edit_roles','delete_roles',
            'view_permissions','create_permissions','edit_permissions','delete_permissions',
            // System entities
            'view_companies','create_companies','edit_companies','delete_companies',
            'view_customers','create_customers','edit_customers','delete_customers',
            'view_employees','create_employees','edit_employees','delete_employees',
            'view_departments','create_departments','edit_departments','delete_departments',
            'view_positions','create_positions','edit_positions','delete_positions',
            'view_helpdeskteams','create_helpdeskteams','edit_helpdeskteams','delete_helpdeskteams',
            'view_tags','create_tags','edit_tags','delete_tags',
            'view_reports','create_reports','edit_reports','delete_reports',
            // Tickets
            'view_tickets','create_tickets','edit_tickets','delete_tickets',
            'view_all_tickets','assign_ticket','change_ticket_status','comment_ticket','export_tickets',
        ];

        $agentPermissions = [
            'view_dashboard', 'view_profile',
            'view_my_tickets', 'view_team_tickets', 'view_tickets',
            'create_tickets', 'edit_tickets', 'comment_ticket',
            'view_customers', 'create_customers', 'edit_customers',
        ];

        $helpdeskPermissions = [
            'view_dashboard', 'view_profile',
            'view_team_tickets', 'view_tickets', 'view_my_tickets',
            'edit_tickets', 'assign_ticket', 'change_ticket_status', 'comment_ticket',
        ];


        // Super Admin Role (all permissions)
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin'], 
            ['description' => 'Has all permissions in the system']
        );
        $allPermissionIds = Permission::pluck('id');
        $superAdminRole->permissions()->sync($allPermissionIds);
        $this->command->info('super-admin role created with all permissions.');

        // Admin Role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator with broad management permissions']
        );
        $adminPermissionIds = Permission::whereIn('name', $adminPermissions)->pluck('id');
        $adminRole->permissions()->sync($adminPermissionIds);
        $this->command->info('admin role created and permissions assigned.');

        // Agent Role
        $agentRole = Role::firstOrCreate(
            ['name' => 'agent'], 
            ['description' => 'Handles customer tickets and profiles']
        );
        $agentPermissionIds = Permission::whereIn('name', $agentPermissions)->pluck('id');
        $agentRole->permissions()->sync($agentPermissionIds);
        $this->command->info('agent role created and permissions assigned.');

        // Helpdesk Role
        $helpdeskRole = Role::firstOrCreate(
            ['name' => 'helpdesk'], 
            ['description' => 'Standard user with basic ticket access']
        );
        $helpdeskPermissionIds = Permission::whereIn('name', $helpdeskPermissions)->pluck('id');
        $helpdeskRole->permissions()->sync($helpdeskPermissionIds);
        $this->command->info('helpdesk role created and permissions assigned.');
    }
}