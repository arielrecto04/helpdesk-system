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
        // Clear role_permissions and permissions so we can reseed cleanly
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Cleared role-permission associations.');

        // Define all permissions exactly as required by the system (menu + show + CRUD + special)
        $allPermissions = [
            ['name' => 'view_dashboard', 'description' => 'Can view the main dashboard'],
            ['name' => 'view_profile', 'description' => 'Can view and edit own profile'],

            ['name' => 'view_my_tickets_menu', 'description' => 'Can view My Tickets menu'],
            ['name' => 'show_my_tickets', 'description' => 'Can view my tickets list'],
            ['name' => 'create_my_tickets', 'description' => 'Can create my tickets'],
            ['name' => 'edit_my_tickets', 'description' => 'Can edit my tickets'],
            ['name' => 'delete_my_tickets', 'description' => 'Can delete my tickets'],

            ['name' => 'view_all_tickets_menu', 'description' => 'Can view All Tickets menu'],
            ['name' => 'show_all_tickets', 'description' => 'Can view all tickets list'],
            ['name' => 'create_all_tickets', 'description' => 'Can create tickets (all)'],
            ['name' => 'edit_all_tickets', 'description' => 'Can edit tickets (all)'],
            ['name' => 'delete_all_tickets', 'description' => 'Can delete tickets (all)'],

            ['name' => 'view_ticket_analysis_menu', 'description' => 'Can view ticket analysis menu'],
            ['name' => 'view_customer_ratings_menu', 'description' => 'Can view customer ratings menu'],

            ['name' => 'view_helpdeskteams_menu', 'description' => 'Can view helpdesk teams menu'],
            ['name' => 'show_helpdeskteams', 'description' => 'Can view helpdesk teams list'],
            ['name' => 'create_helpdeskteams', 'description' => 'Can create helpdesk teams'],
            ['name' => 'edit_helpdeskteams', 'description' => 'Can edit helpdesk teams'],
            ['name' => 'delete_helpdeskteams', 'description' => 'Can delete helpdesk teams'],

            ['name' => 'view_users_menu', 'description' => 'Can view users menu'],
            ['name' => 'show_users', 'description' => 'Can view users list/detail'],
            ['name' => 'create_users', 'description' => 'Can create users'],
            ['name' => 'edit_users', 'description' => 'Can edit users'],
            ['name' => 'delete_users', 'description' => 'Can delete users'],

            ['name' => 'view_employees_menu', 'description' => 'Can view employees menu'],
            ['name' => 'show_employees', 'description' => 'Can view employees list/detail'],
            ['name' => 'create_employees', 'description' => 'Can create employees'],
            ['name' => 'edit_employees', 'description' => 'Can edit employees'],
            ['name' => 'delete_employees', 'description' => 'Can delete employees'],

            ['name' => 'view_customers_menu', 'description' => 'Can view customers menu'],
            ['name' => 'show_customers', 'description' => 'Can view customers list/detail'],
            ['name' => 'create_customers', 'description' => 'Can create customers'],
            ['name' => 'edit_customers', 'description' => 'Can edit customers'],
            ['name' => 'delete_customers', 'description' => 'Can delete customers'],

            ['name' => 'view_departments_menu', 'description' => 'Can view departments menu'],
            ['name' => 'show_departments', 'description' => 'Can view departments list/detail'],
            ['name' => 'create_departments', 'description' => 'Can create departments'],
            ['name' => 'edit_departments', 'description' => 'Can edit departments'],
            ['name' => 'delete_departments', 'description' => 'Can delete departments'],

            ['name' => 'view_tags_menu', 'description' => 'Can view tags menu'],
            ['name' => 'show_tags', 'description' => 'Can view tags list/detail'],
            ['name' => 'create_tags', 'description' => 'Can create tags'],
            ['name' => 'edit_tags', 'description' => 'Can edit tags'],
            ['name' => 'delete_tags', 'description' => 'Can delete tags'],

            ['name' => 'view_roles_menu', 'description' => 'Can view roles menu'],
            ['name' => 'show_roles', 'description' => 'Can view roles list/detail'],
            ['name' => 'create_roles', 'description' => 'Can create roles'],
            ['name' => 'edit_roles', 'description' => 'Can edit roles'],
            ['name' => 'delete_roles', 'description' => 'Can delete roles'],

            ['name' => 'view_companies_menu', 'description' => 'Can view companies menu'],
            ['name' => 'show_companies', 'description' => 'Can view companies list/detail'],
            ['name' => 'create_companies', 'description' => 'Can create companies'],
            ['name' => 'edit_companies', 'description' => 'Can edit companies'],
            ['name' => 'delete_companies', 'description' => 'Can delete companies'],

            ['name' => 'view_canned_responses_menu', 'description' => 'Can view canned responses menu'],
            ['name' => 'show_canned_responses', 'description' => 'Can view canned responses list/detail'],
            ['name' => 'create_canned_responses', 'description' => 'Can create canned responses'],
            ['name' => 'edit_canned_responses', 'description' => 'Can edit canned responses'],
            ['name' => 'delete_canned_responses', 'description' => 'Can delete canned responses'],

            ['name' => 'view_logs_menu', 'description' => 'Can view logs menu'],
            ['name' => 'show_logs', 'description' => 'Can view logs list/detail'],
            ['name' => 'create_logs', 'description' => 'Can create logs'],
            ['name' => 'edit_logs', 'description' => 'Can edit logs'],
            ['name' => 'delete_logs', 'description' => 'Can delete logs'],

            ['name' => 'view_permissions_menu', 'description' => 'Can view permissions menu'],
            ['name' => 'show_permissions', 'description' => 'Can view permissions list/detail'],
            ['name' => 'create_permissions', 'description' => 'Can create permissions'],
            ['name' => 'edit_permissions', 'description' => 'Can edit permissions'],
            ['name' => 'delete_permissions', 'description' => 'Can delete permissions'],

            ['name' => 'view_positions_menu', 'description' => 'Can view positions menu'],
            ['name' => 'show_positions', 'description' => 'Can view positions list/detail'],
            ['name' => 'create_positions', 'description' => 'Can create positions'],
            ['name' => 'edit_positions', 'description' => 'Can edit positions'],
            ['name' => 'delete_positions', 'description' => 'Can delete positions'],

            ['name' => 'can_view_other_locations_tickets', 'description' => 'Can view tickets in other locations'],
            ['name' => 'can_view_other_teams_tickets', 'description' => 'Can view tickets for other teams'],
            ['name' => 'can_view_other_users_tickets', 'description' => 'Can view tickets for other users'],
        ];

        // Create all permissions (idempotent)
        foreach ($allPermissions as $permission) {
            // Use name as the lookup key to avoid duplicate insertion when descriptions change
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description'] ?? null]
            );
        }

        $this->command->info('All application permissions have been seeded.');

        // Define role permission sets for non-admin roles
        // Agent permissions: only use permission names from the approved list
        $agentPermissions = [
            'view_dashboard', 'view_profile',
            'view_my_tickets_menu', 'show_my_tickets', 'create_my_tickets', 'edit_my_tickets',
            'view_all_tickets_menu', 'show_all_tickets',
            'view_customers_menu', 'show_customers', 'create_customers', 'edit_customers',
        ];

        // Helpdesk permissions: only use permission names from the approved list
        $helpdeskPermissions = [
            'view_dashboard', 'view_profile',
            'view_my_tickets_menu', 'show_my_tickets', 'edit_my_tickets',
            'view_all_tickets_menu', 'show_all_tickets',
            'can_view_other_teams_tickets', 'can_view_other_users_tickets',
        ];

        $adminPermissions = [
            'view_dashboard', 'view_profile',
            'view_my_tickets_menu','show_my_tickets','create_my_tickets','edit_my_tickets','delete_my_tickets',
            'view_all_tickets_menu','show_all_tickets','create_all_tickets','edit_all_tickets','delete_all_tickets',
            'view_ticket_analysis_menu',
            'view_customer_ratings_menu',
            'view_helpdeskteams_menu','show_helpdeskteams','create_helpdeskteams','edit_helpdeskteams','delete_helpdeskteams',
            'view_users_menu','show_users','create_users','edit_users','delete_users',
            'view_employees_menu','show_employees','create_employees','edit_employees','delete_employees',
            'view_customers_menu','show_customers','create_customers','edit_customers','delete_customers',
            'view_departments_menu','show_departments','create_departments','edit_departments','delete_departments',
            'view_tags_menu','show_tags','create_tags','edit_tags','delete_tags',
            'view_roles_menu','show_roles','create_roles','edit_roles','delete_roles',
            'view_companies_menu','show_companies','create_companies','edit_companies','delete_companies',
            'view_canned_responses_menu','show_canned_responses','create_canned_responses','edit_canned_responses','delete_canned_responses',
            'view_logs_menu','show_logs','create_logs','edit_logs','delete_logs',
            'view_permissions_menu','show_permissions','create_permissions','edit_permissions','delete_permissions',
            'view_positions_menu','show_positions','create_positions','edit_positions','delete_positions',
            'can_view_other_locations_tickets','can_view_other_teams_tickets','can_view_other_users_tickets',
            
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