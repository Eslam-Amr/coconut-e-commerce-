<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'name' => 'super_admin',
                'description' => 'Super Administrator with full access to all features'
            ],
            [
                'name' => 'admin',
                'description' => 'Administrator with most access except system management'
            ],
            [
                'name' => 'product_manager',
                'description' => 'Product Manager with access to product-related features'
            ],
            [
                'name' => 'content_manager',
                'description' => 'Content Manager with access to banners, sliders, and content'
            ],
            [
                'name' => 'sales_manager',
                'description' => 'Sales Manager with access to orders, vouchers, and sales features'
            ],
            [
                'name' => 'location_manager',
                'description' => 'Location Manager with access to countries, cities, and districts'
            ],
            [
                'name' => 'support_staff',
                'description' => 'Support Staff with limited access to view and basic operations'
            ],
            [
                'name' => 'viewer',
                'description' => 'Viewer with read-only access to most features'
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Assign permissions based on role
            $this->assignPermissionsToRole($role);
        }
    }

    /**
     * Assign permissions to specific roles
     */
    private function assignPermissionsToRole(Role $role): void
    {
        switch ($role->name) {
            case 'super_admin':
                $this->assignAllPermissions($role);
                break;

            case 'admin':
                $this->assignAdminPermissions($role);
                break;

            case 'product_manager':
                $this->assignProductManagerPermissions($role);
                break;

            case 'content_manager':
                $this->assignContentManagerPermissions($role);
                break;

            case 'sales_manager':
                $this->assignSalesManagerPermissions($role);
                break;

            case 'location_manager':
                $this->assignLocationManagerPermissions($role);
                break;

            case 'support_staff':
                $this->assignSupportStaffPermissions($role);
                break;

            case 'viewer':
                $this->assignViewerPermissions($role);
                break;
        }
    }

    /**
     * Assign all permissions to super admin
     */
    private function assignAllPermissions(Role $role): void
    {
        $allPermissions = Permission::all();
        $role->permissions()->sync($allPermissions->pluck('id'));
    }

    /**
     * Assign permissions for admin role
     */
    private function assignAdminPermissions(Role $role): void
    {
        $permissions = [
            // Authentication
            'auth.login', 'auth.logout',
            
            // Admin Management
            'admins.view', 'admins.create', 'admins.update', 'admins.delete', 'admins.toggle_active',
            'admins.assign_role', 'admins.remove_role',
            'admins.grant_permission', 'admins.revoke_permission', 'admins.remove_direct_permission',
            'admins.get_available_roles', 'admins.get_available_permissions',
            
            // User Management
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.toggle_active',
            
            // Role & Permission Management
            'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign',
            'permissions.view', 'permissions.create', 'permissions.update', 'permissions.delete', 'permissions.assign',
            
            // Product Management
            'products.view', 'products.create', 'products.update', 'products.delete', 'products.toggle_active',
            'product_attributes.view', 'product_attributes.create', 'product_attributes.update', 'product_attributes.delete',
            'product_attributes.bulk_assign', 'product_attributes.remove_all', 'product_attributes.get_available',
            'product_variants.view', 'product_variants.create', 'product_variants.update', 'product_variants.delete',
            'product_variants.assign_attributes', 'product_variants.remove_attributes', 'product_variants.view_with_attributes',
            
            // Category & Brand Management
            'categories.view', 'categories.create', 'categories.update', 'categories.delete', 'categories.toggle_active',
            'brands.view', 'brands.create', 'brands.update', 'brands.delete', 'brands.toggle_active',
            
            // Attribute Management
            'attributes.view', 'attributes.create', 'attributes.update', 'attributes.delete', 'attributes.toggle_active',
            'attribute_values.view', 'attribute_values.create', 'attribute_values.update', 'attribute_values.delete', 'attribute_values.toggle_active',
            
            // Content Management
            'banners.view', 'banners.create', 'banners.update', 'banners.delete', 'banners.toggle_active',
            'sliders.view', 'sliders.create', 'sliders.update', 'sliders.delete', 'sliders.toggle_active',
            'static_pages.view', 'static_pages.create', 'static_pages.update', 'static_pages.delete',
            
            // Sales Management
            'flash_sales.view', 'flash_sales.create', 'flash_sales.update', 'flash_sales.delete', 'flash_sales.toggle_active',
            'vouchers.view', 'vouchers.create', 'vouchers.update', 'vouchers.delete', 'vouchers.toggle_active',
            
            // Location Management
            'countries.view', 'countries.create', 'countries.update', 'countries.delete', 'countries.toggle_active',
            'cities.view', 'cities.create', 'cities.update', 'cities.delete', 'cities.toggle_active',
            'districts.view', 'districts.create', 'districts.update', 'districts.delete', 'districts.toggle_active',
            
            // Order Management
            'orders.view', 'orders.update', 'orders.delete', 'orders.manage_status',
            
            // Inventory Management
            'inventory.view', 'inventory.update', 'inventory.manage',
            
            // Media Management
            'media.upload', 'media.delete', 'media.manage',
            
            // Reports & Analytics
            'reports.view', 'reports.export', 'analytics.view',
            
            // Settings Management
            'settings.view', 'settings.update', 'settings.manage',
            
            // Profile Management
            'profile.view', 'profile.update', 'profile.change_password',
            
            // Notifications
            'notifications.view', 'notifications.send', 'notifications.manage',
            
            // Dashboard Access
            'dashboard.access', 'dashboard.overview',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Assign permissions for product manager role
     */
    private function assignProductManagerPermissions(Role $role): void
    {
        $permissions = [
            // Authentication
            'auth.login', 'auth.logout',
            
            // Product Management
            'products.view', 'products.create', 'products.update', 'products.delete', 'products.toggle_active',
            'product_attributes.view', 'product_attributes.create', 'product_attributes.update', 'product_attributes.delete',
            'product_attributes.bulk_assign', 'product_attributes.remove_all', 'product_attributes.get_available',
            'product_variants.view', 'product_variants.create', 'product_variants.update', 'product_variants.delete',
            'product_variants.assign_attributes', 'product_variants.remove_attributes', 'product_variants.view_with_attributes',
            
            // Category & Brand Management
            'categories.view', 'categories.create', 'categories.update', 'categories.delete', 'categories.toggle_active',
            'brands.view', 'brands.create', 'brands.update', 'brands.delete', 'brands.toggle_active',
            
            // Attribute Management
            'attributes.view', 'attributes.create', 'attributes.update', 'attributes.delete', 'attributes.toggle_active',
            'attribute_values.view', 'attribute_values.create', 'attribute_values.update', 'attribute_values.delete', 'attribute_values.toggle_active',
            
            // Inventory Management
            'inventory.view', 'inventory.update', 'inventory.manage',
            
            // Media Management
            'media.upload', 'media.delete',
            
            // Profile Management
            'profile.view', 'profile.update', 'profile.change_password',
            
            // Dashboard Access
            'dashboard.access', 'dashboard.overview',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Assign permissions for content manager role
     */
    private function assignContentManagerPermissions(Role $role): void
    {
        $permissions = [
            // Authentication
            'auth.login', 'auth.logout',
            
            // Content Management
            'banners.view', 'banners.create', 'banners.update', 'banners.delete', 'banners.toggle_active',
            'sliders.view', 'sliders.create', 'sliders.update', 'sliders.delete', 'sliders.toggle_active',
            'static_pages.view', 'static_pages.create', 'static_pages.update', 'static_pages.delete',
            'static_pages.view', 'static_pages.create', 'static_pages.update', 'static_pages.delete',
            
            // Category Management (for content organization)
            'categories.view', 'categories.create', 'categories.update', 'categories.toggle_active',
            
            // Media Management
            'media.upload', 'media.delete',
            
            // Profile Management
            'profile.view', 'profile.update', 'profile.change_password',
            
            // Dashboard Access
            'dashboard.access', 'dashboard.overview',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Assign permissions for sales manager role
     */
    private function assignSalesManagerPermissions(Role $role): void
    {
        $permissions = [
            // Authentication
            'auth.login', 'auth.logout',
            
            // Sales Management
            'flash_sales.view', 'flash_sales.create', 'flash_sales.update', 'flash_sales.delete', 'flash_sales.toggle_active',
            'vouchers.view', 'vouchers.create', 'vouchers.update', 'vouchers.delete', 'vouchers.toggle_active',
            
            // Order Management
            'orders.view', 'orders.update', 'orders.manage_status',
            
            // Product Management (view only for sales context)
            'products.view',
            'categories.view', 'brands.view',
            
            // Reports & Analytics
            'reports.view', 'reports.export', 'analytics.view',
            
            // Profile Management
            'profile.view', 'profile.update', 'profile.change_password',
            
            // Dashboard Access
            'dashboard.access', 'dashboard.overview',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Assign permissions for location manager role
     */
    private function assignLocationManagerPermissions(Role $role): void
    {
        $permissions = [
            // Authentication
            'auth.login', 'auth.logout',
            
            // Location Management
            'countries.view', 'countries.create', 'countries.update', 'countries.delete', 'countries.toggle_active',
            'cities.view', 'cities.create', 'cities.update', 'cities.delete', 'cities.toggle_active',
            'districts.view', 'districts.create', 'districts.update', 'districts.delete', 'districts.toggle_active',
            
            // Profile Management
            'profile.view', 'profile.update', 'profile.change_password',
            
            // Dashboard Access
            'dashboard.access', 'dashboard.overview',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Assign permissions for support staff role
     */
    private function assignSupportStaffPermissions(Role $role): void
    {
        $permissions = [
            // Authentication
            'auth.login', 'auth.logout',
            
            // Order Management (limited)
            'orders.view', 'orders.update',
            
            // User Management (view only)
            'users.view',
            
            // Product Management (view only)
            'products.view',
            'categories.view', 'brands.view',
            
            // Profile Management
            'profile.view', 'profile.update', 'profile.change_password',
            
            // Dashboard Access
            'dashboard.access',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Assign permissions for viewer role
     */
    private function assignViewerPermissions(Role $role): void
    {
        $permissions = [
            // Authentication
            'auth.login', 'auth.logout',
            
            // View-only permissions
            'users.view',
            'products.view',
            'categories.view', 'brands.view',
            'attributes.view', 'attribute_values.view',
            'banners.view', 'sliders.view', 'static_pages.view',
            'flash_sales.view', 'vouchers.view',
            'countries.view', 'cities.view', 'districts.view',
            'orders.view',
            'inventory.view',
            'reports.view', 'analytics.view',
            'settings.view',
            
            // Profile Management
            'profile.view', 'profile.update', 'profile.change_password',
            
            // Dashboard Access
            'dashboard.access', 'dashboard.overview',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
    }
}
