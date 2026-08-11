<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management
            ['name' => 'view_users', 'display_name' => 'View Users'],
            ['name' => 'create_users', 'display_name' => 'Create Users'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users'],
            ['name' => 'delete_users', 'display_name' => 'Delete Users'],
            ['name' => 'suspend_users', 'display_name' => 'Suspend Users'],
            ['name' => 'activate_users', 'display_name' => 'Activate Users'],

            // Profile Management
            ['name' => 'edit_own_profile', 'display_name' => 'Edit Own Profile'],
            ['name' => 'edit_own_password', 'display_name' => 'Edit Own Password'],
            ['name' => 'edit_profile_picture', 'display_name' => 'Edit Profile Picture'],

            // Farmer Management
            ['name' => 'view_farms', 'display_name' => 'View Farms'],
            ['name' => 'create_farms', 'display_name' => 'Create Farms'],
            ['name' => 'edit_farms', 'display_name' => 'Edit Farms'],
            ['name' => 'delete_farms', 'display_name' => 'Delete Farms'],
            ['name' => 'upload_farm_images', 'display_name' => 'Upload Farm Images'],
            ['name' => 'upload_farm_documents', 'display_name' => 'Upload Farm Documents'],

            // Crop Management
            ['name' => 'view_crops', 'display_name' => 'View Crops'],
            ['name' => 'create_crops', 'display_name' => 'Create Crops'],
            ['name' => 'edit_crops', 'display_name' => 'Edit Crops'],
            ['name' => 'delete_crops', 'display_name' => 'Delete Crops'],
            ['name' => 'record_harvest', 'display_name' => 'Record Harvest'],

            // Product Management
            ['name' => 'view_products', 'display_name' => 'View Products'],
            ['name' => 'create_products', 'display_name' => 'Create Products'],
            ['name' => 'edit_products', 'display_name' => 'Edit Products'],
            ['name' => 'delete_products', 'display_name' => 'Delete Products'],
            ['name' => 'upload_product_images', 'display_name' => 'Upload Product Images'],
            ['name' => 'publish_products', 'display_name' => 'Publish Products'],

            // Order Management
            ['name' => 'view_orders', 'display_name' => 'View Orders'],
            ['name' => 'create_orders', 'display_name' => 'Create Orders'],
            ['name' => 'accept_orders', 'display_name' => 'Accept Orders'],
            ['name' => 'reject_orders', 'display_name' => 'Reject Orders'],
            ['name' => 'cancel_orders', 'display_name' => 'Cancel Orders'],
            ['name' => 'view_all_orders', 'display_name' => 'View All Orders'],

            // Payment Management
            ['name' => 'view_payments', 'display_name' => 'View Payments'],
            ['name' => 'process_payments', 'display_name' => 'Process Payments'],
            ['name' => 'view_all_payments', 'display_name' => 'View All Payments'],
            ['name' => 'refund_payments', 'display_name' => 'Refund Payments'],

            // Delivery Management
            ['name' => 'view_deliveries', 'display_name' => 'View Deliveries'],
            ['name' => 'request_delivery', 'display_name' => 'Request Delivery'],
            ['name' => 'accept_delivery', 'display_name' => 'Accept Delivery'],
            ['name' => 'update_delivery_tracking', 'display_name' => 'Update Delivery Tracking'],
            ['name' => 'view_all_deliveries', 'display_name' => 'View All Deliveries'],

            // Inventory Management
            ['name' => 'view_inventory', 'display_name' => 'View Inventory'],
            ['name' => 'manage_inventory', 'display_name' => 'Manage Inventory'],
            ['name' => 'view_warehouse', 'display_name' => 'View Warehouse'],
            ['name' => 'manage_warehouse', 'display_name' => 'Manage Warehouse'],

            // Consultation Management
            ['name' => 'view_consultations', 'display_name' => 'View Consultations'],
            ['name' => 'request_consultation', 'display_name' => 'Request Consultation'],
            ['name' => 'respond_to_consultation', 'display_name' => 'Respond to Consultation'],
            ['name' => 'create_training_materials', 'display_name' => 'Create Training Materials'],

            // Loan Management
            ['name' => 'view_loans', 'display_name' => 'View Loans'],
            ['name' => 'apply_for_loan', 'display_name' => 'Apply for Loan'],
            ['name' => 'view_loan_applications', 'display_name' => 'View Loan Applications'],
            ['name' => 'approve_loan', 'display_name' => 'Approve Loan'],
            ['name' => 'reject_loan', 'display_name' => 'Reject Loan'],
            ['name' => 'manage_insurance', 'display_name' => 'Manage Insurance'],

            // Cooperative Management
            ['name' => 'view_cooperative_members', 'display_name' => 'View Cooperative Members'],
            ['name' => 'manage_cooperative_members', 'display_name' => 'Manage Cooperative Members'],
            ['name' => 'view_cooperative_sales', 'display_name' => 'View Cooperative Sales'],

            // Reports
            ['name' => 'view_own_reports', 'display_name' => 'View Own Reports'],
            ['name' => 'view_all_reports', 'display_name' => 'View All Reports'],
            ['name' => 'export_reports', 'display_name' => 'Export Reports'],

            // Dashboard
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard'],
            ['name' => 'view_admin_statistics', 'display_name' => 'View Admin Statistics'],

            // Cart Management
            ['name' => 'manage_cart', 'display_name' => 'Manage Cart'],
            ['name' => 'checkout', 'display_name' => 'Checkout'],

            // Notifications
            ['name' => 'view_notifications', 'display_name' => 'View Notifications'],
            ['name' => 'manage_notification_settings', 'display_name' => 'Manage Notification Settings'],

            // Category Management
            ['name' => 'view_categories', 'display_name' => 'View Categories'],
            ['name' => 'create_categories', 'display_name' => 'Create Categories'],
            ['name' => 'edit_categories', 'display_name' => 'Edit Categories'],
            ['name' => 'delete_categories', 'display_name' => 'Delete Categories'],

            // System Management
            ['name' => 'view_system_settings', 'display_name' => 'View System Settings'],
            ['name' => 'edit_system_settings', 'display_name' => 'Edit System Settings'],
            ['name' => 'view_activity_logs', 'display_name' => 'View Activity Logs'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles'],
            ['name' => 'manage_permissions', 'display_name' => 'Manage Permissions'],

            // Marketplace
            ['name' => 'view_marketplace', 'display_name' => 'View Marketplace'],
            ['name' => 'search_products', 'display_name' => 'Search Products'],
            ['name' => 'add_product_review', 'display_name' => 'Add Product Review'],
            ['name' => 'manage_wishlist', 'display_name' => 'Manage Wishlist'],

            // Weather & Market Info
            ['name' => 'view_weather', 'display_name' => 'View Weather'],
            ['name' => 'view_market_prices', 'display_name' => 'View Market Prices'],
        ];

        // Insert permissions
        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $adminPermissions = DB::table('permissions')->pluck('id', 'name')->toArray();
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        // Admin has all permissions
        foreach ($adminPermissions as $permission) {
            DB::table('permission_role')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permission,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Farmer permissions
        $farmerRoleId = DB::table('roles')->where('name', 'farmer')->value('id');
        $farmerPerms = [
            'view_farms', 'create_farms', 'edit_farms', 'delete_farms',
            'upload_farm_images', 'upload_farm_documents', 'view_crops', 'create_crops',
            'edit_crops', 'delete_crops', 'record_harvest', 'create_products',
            'edit_products', 'delete_products', 'upload_product_images',
            'publish_products', 'view_orders', 'accept_orders', 'reject_orders',
            'view_payments', 'request_delivery', 'view_deliveries',
            'view_consultations', 'request_consultation', 'apply_for_loan',
            'view_loans', 'view_own_reports', 'view_dashboard', 'manage_cart',
            'checkout', 'view_notifications', 'manage_notification_settings',
            'view_categories', 'view_marketplace', 'search_products',
            'add_product_review', 'manage_wishlist', 'view_weather',
            'view_market_prices', 'edit_own_profile', 'edit_own_password',
            'edit_profile_picture'
        ];
        $this->assignPermissions($farmerRoleId, $farmerPerms, $adminPermissions);

        // Buyer permissions
        $buyerRoleId = DB::table('roles')->where('name', 'buyer')->value('id');
        $buyerPerms = [
            'view_products', 'view_categories', 'manage_cart', 'checkout',
            'create_orders', 'view_orders', 'view_payments', 'view_deliveries',
            'view_marketplace', 'search_products', 'add_product_review',
            'manage_wishlist', 'view_weather', 'view_market_prices',
            'view_dashboard', 'view_notifications', 'manage_notification_settings',
            'edit_own_profile', 'edit_own_password', 'edit_profile_picture'
        ];
        $this->assignPermissions($buyerRoleId, $buyerPerms, $adminPermissions);

        // Supplier permissions
        $supplierRoleId = DB::table('roles')->where('name', 'supplier')->value('id');
        $supplierPerms = [
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'upload_product_images', 'view_inventory', 'manage_inventory',
            'view_warehouse', 'manage_warehouse', 'view_orders', 'accept_orders',
            'view_payments', 'view_all_reports', 'view_dashboard',
            'view_notifications', 'manage_notification_settings',
            'edit_own_profile', 'edit_own_password', 'edit_profile_picture'
        ];
        $this->assignPermissions($supplierRoleId, $supplierPerms, $adminPermissions);

        // Transport permissions
        $transportRoleId = DB::table('roles')->where('name', 'transport')->value('id');
        $transportPerms = [
            'view_deliveries', 'accept_delivery', 'update_delivery_tracking',
            'view_payments', 'view_all_reports', 'view_dashboard',
            'view_notifications', 'manage_notification_settings',
            'edit_own_profile', 'edit_own_password', 'edit_profile_picture'
        ];
        $this->assignPermissions($transportRoleId, $transportPerms, $adminPermissions);

        // Expert permissions
        $expertRoleId = DB::table('roles')->where('name', 'expert')->value('id');
        $expertPerms = [
            'view_consultations', 'respond_to_consultation', 'create_training_materials',
            'view_all_reports', 'view_dashboard', 'view_notifications',
            'manage_notification_settings', 'edit_own_profile', 'edit_own_password',
            'edit_profile_picture'
        ];
        $this->assignPermissions($expertRoleId, $expertPerms, $adminPermissions);

        // Financial institution permissions
        $financialRoleId = DB::table('roles')->where('name', 'financial')->value('id');
        $financialPerms = [
            'view_loan_applications', 'approve_loan', 'reject_loan',
            'manage_insurance', 'view_payments', 'process_payments',
            'view_all_reports', 'view_dashboard', 'view_notifications',
            'manage_notification_settings', 'edit_own_profile', 'edit_own_password',
            'edit_profile_picture'
        ];
        $this->assignPermissions($financialRoleId, $financialPerms, $adminPermissions);

        // Cooperative permissions
        $cooperativeRoleId = DB::table('roles')->where('name', 'cooperative')->value('id');
        $cooperativePerms = [
            'view_cooperative_members', 'manage_cooperative_members',
            'view_cooperative_sales', 'view_orders', 'view_payments',
            'view_all_reports', 'view_dashboard', 'view_notifications',
            'manage_notification_settings', 'edit_own_profile', 'edit_own_password',
            'edit_profile_picture'
        ];
        $this->assignPermissions($cooperativeRoleId, $cooperativePerms, $adminPermissions);
    }

    private function assignPermissions(int $roleId, array $permissionNames, array $allPermissions): void
    {
        foreach ($permissionNames as $permName) {
            if (isset($allPermissions[$permName])) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $allPermissions[$permName],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
