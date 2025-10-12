<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Authentication & Authorization
            ['name' => 'auth.login', 'description' => 'Can login to dashboard'],
            ['name' => 'auth.logout', 'description' => 'Can logout from dashboard'],
            
            // User Management
            ['name' => 'users.view', 'description' => 'Can view users'],
            ['name' => 'users.create', 'description' => 'Can create users'],
            ['name' => 'users.update', 'description' => 'Can update users'],
            ['name' => 'users.delete', 'description' => 'Can delete users'],
            ['name' => 'users.toggle_active', 'description' => 'Can toggle user active status'],
            
            // Role & Permission Management
            ['name' => 'roles.view', 'description' => 'Can view roles'],
            ['name' => 'roles.create', 'description' => 'Can create roles'],
            ['name' => 'roles.update', 'description' => 'Can update roles'],
            ['name' => 'roles.delete', 'description' => 'Can delete roles'],
            ['name' => 'roles.assign', 'description' => 'Can assign roles to users'],
            
            ['name' => 'permissions.view', 'description' => 'Can view permissions'],
            ['name' => 'permissions.create', 'description' => 'Can create permissions'],
            ['name' => 'permissions.update', 'description' => 'Can update permissions'],
            ['name' => 'permissions.delete', 'description' => 'Can delete permissions'],
            ['name' => 'permissions.assign', 'description' => 'Can assign permissions to roles'],
            
            // Product Management
            ['name' => 'products.view', 'description' => 'Can view products'],
            ['name' => 'products.create', 'description' => 'Can create products'],
            ['name' => 'products.update', 'description' => 'Can update products'],
            ['name' => 'products.delete', 'description' => 'Can delete products'],
            ['name' => 'products.toggle_active', 'description' => 'Can toggle product active status'],
            
            // Product Attributes
            ['name' => 'product_attributes.view', 'description' => 'Can view product attributes'],
            ['name' => 'product_attributes.create', 'description' => 'Can create product attributes'],
            ['name' => 'product_attributes.update', 'description' => 'Can update product attributes'],
            ['name' => 'product_attributes.delete', 'description' => 'Can delete product attributes'],
            ['name' => 'product_attributes.bulk_assign', 'description' => 'Can bulk assign attributes to products'],
            ['name' => 'product_attributes.remove_all', 'description' => 'Can remove all attributes from products'],
            ['name' => 'product_attributes.get_available', 'description' => 'Can get available attributes for products'],
            
            // Product Variants
            ['name' => 'product_variants.view', 'description' => 'Can view product variants'],
            ['name' => 'product_variants.create', 'description' => 'Can create product variants'],
            ['name' => 'product_variants.update', 'description' => 'Can update product variants'],
            ['name' => 'product_variants.delete', 'description' => 'Can delete product variants'],
            ['name' => 'product_variants.assign_attributes', 'description' => 'Can assign attributes to variants'],
            ['name' => 'product_variants.remove_attributes', 'description' => 'Can remove attributes from variants'],
            ['name' => 'product_variants.view_with_attributes', 'description' => 'Can view variants with attributes'],
            
            // Category Management
            ['name' => 'categories.view', 'description' => 'Can view categories'],
            ['name' => 'categories.create', 'description' => 'Can create categories'],
            ['name' => 'categories.update', 'description' => 'Can update categories'],
            ['name' => 'categories.delete', 'description' => 'Can delete categories'],
            ['name' => 'categories.toggle_active', 'description' => 'Can toggle category active status'],
            
            // Brand Management
            ['name' => 'brands.view', 'description' => 'Can view brands'],
            ['name' => 'brands.create', 'description' => 'Can create brands'],
            ['name' => 'brands.update', 'description' => 'Can update brands'],
            ['name' => 'brands.delete', 'description' => 'Can delete brands'],
            ['name' => 'brands.toggle_active', 'description' => 'Can toggle brand active status'],
            
            // Attribute Management
            ['name' => 'attributes.view', 'description' => 'Can view attributes'],
            ['name' => 'attributes.create', 'description' => 'Can create attributes'],
            ['name' => 'attributes.update', 'description' => 'Can update attributes'],
            ['name' => 'attributes.delete', 'description' => 'Can delete attributes'],
            ['name' => 'attributes.toggle_active', 'description' => 'Can toggle attribute active status'],
            
            // Attribute Value Management
            ['name' => 'attribute_values.view', 'description' => 'Can view attribute values'],
            ['name' => 'attribute_values.create', 'description' => 'Can create attribute values'],
            ['name' => 'attribute_values.update', 'description' => 'Can update attribute values'],
            ['name' => 'attribute_values.delete', 'description' => 'Can delete attribute values'],
            ['name' => 'attribute_values.toggle_active', 'description' => 'Can toggle attribute value active status'],
            
            // Banner Management
            ['name' => 'banners.view', 'description' => 'Can view banners'],
            ['name' => 'banners.create', 'description' => 'Can create banners'],
            ['name' => 'banners.update', 'description' => 'Can update banners'],
            ['name' => 'banners.delete', 'description' => 'Can delete banners'],
            ['name' => 'banners.toggle_active', 'description' => 'Can toggle banner active status'],
            
            // Slider Management
            ['name' => 'sliders.view', 'description' => 'Can view sliders'],
            ['name' => 'sliders.create', 'description' => 'Can create sliders'],
            ['name' => 'sliders.update', 'description' => 'Can update sliders'],
            ['name' => 'sliders.delete', 'description' => 'Can delete sliders'],
            ['name' => 'sliders.toggle_active', 'description' => 'Can toggle slider active status'],
            
            // Flash Sale Management
            ['name' => 'flash_sales.view', 'description' => 'Can view flash sales'],
            ['name' => 'flash_sales.create', 'description' => 'Can create flash sales'],
            ['name' => 'flash_sales.update', 'description' => 'Can update flash sales'],
            ['name' => 'flash_sales.delete', 'description' => 'Can delete flash sales'],
            ['name' => 'flash_sales.toggle_active', 'description' => 'Can toggle flash sale active status'],
            
            // Voucher Management
            ['name' => 'vouchers.view', 'description' => 'Can view vouchers'],
            ['name' => 'vouchers.create', 'description' => 'Can create vouchers'],
            ['name' => 'vouchers.update', 'description' => 'Can update vouchers'],
            ['name' => 'vouchers.delete', 'description' => 'Can delete vouchers'],
            ['name' => 'vouchers.toggle_active', 'description' => 'Can toggle voucher active status'],
            
            // Location Management
            ['name' => 'countries.view', 'description' => 'Can view countries'],
            ['name' => 'countries.create', 'description' => 'Can create countries'],
            ['name' => 'countries.update', 'description' => 'Can update countries'],
            ['name' => 'countries.delete', 'description' => 'Can delete countries'],
            ['name' => 'countries.toggle_active', 'description' => 'Can toggle country active status'],
            
            ['name' => 'cities.view', 'description' => 'Can view cities'],
            ['name' => 'cities.create', 'description' => 'Can create cities'],
            ['name' => 'cities.update', 'description' => 'Can update cities'],
            ['name' => 'cities.delete', 'description' => 'Can delete cities'],
            ['name' => 'cities.toggle_active', 'description' => 'Can toggle city active status'],
            
            ['name' => 'districts.view', 'description' => 'Can view districts'],
            ['name' => 'districts.create', 'description' => 'Can create districts'],
            ['name' => 'districts.update', 'description' => 'Can update districts'],
            ['name' => 'districts.delete', 'description' => 'Can delete districts'],
            ['name' => 'districts.toggle_active', 'description' => 'Can toggle district active status'],
            
            // Order Management
            ['name' => 'orders.view', 'description' => 'Can view orders'],
            ['name' => 'orders.update', 'description' => 'Can update orders'],
            ['name' => 'orders.delete', 'description' => 'Can delete orders'],
            ['name' => 'orders.manage_status', 'description' => 'Can manage order status'],
            
            // Inventory Management
            ['name' => 'inventory.view', 'description' => 'Can view inventory'],
            ['name' => 'inventory.update', 'description' => 'Can update inventory'],
            ['name' => 'inventory.manage', 'description' => 'Can manage inventory levels'],
            
            // Media Management
            ['name' => 'media.upload', 'description' => 'Can upload media files'],
            ['name' => 'media.delete', 'description' => 'Can delete media files'],
            ['name' => 'media.manage', 'description' => 'Can manage all media files'],
            
            // Reports & Analytics
            ['name' => 'reports.view', 'description' => 'Can view reports'],
            ['name' => 'reports.export', 'description' => 'Can export reports'],
            ['name' => 'analytics.view', 'description' => 'Can view analytics'],
            
            // Settings Management
            ['name' => 'settings.view', 'description' => 'Can view settings'],
            ['name' => 'settings.update', 'description' => 'Can update settings'],
            ['name' => 'settings.manage', 'description' => 'Can manage all settings'],
            
            // Profile Management (for admins)
            ['name' => 'profile.view', 'description' => 'Can view own profile'],
            ['name' => 'profile.update', 'description' => 'Can update own profile'],
            ['name' => 'profile.change_password', 'description' => 'Can change own password'],
            
            // Notifications
            ['name' => 'notifications.view', 'description' => 'Can view notifications'],
            ['name' => 'notifications.send', 'description' => 'Can send notifications'],
            ['name' => 'notifications.manage', 'description' => 'Can manage all notifications'],
            
            // Dashboard Access
            ['name' => 'dashboard.access', 'description' => 'Can access dashboard'],
            ['name' => 'dashboard.overview', 'description' => 'Can view dashboard overview'],
            
            // System Management
            ['name' => 'system.logs', 'description' => 'Can view system logs'],
            ['name' => 'system.backup', 'description' => 'Can manage system backups'],
            ['name' => 'system.maintenance', 'description' => 'Can perform system maintenance'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
