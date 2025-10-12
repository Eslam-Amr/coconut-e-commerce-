# Permissions and Roles System

This document explains the permissions and roles system implemented in the e-commerce application.

## Overview

The application uses a role-based access control (RBAC) system with the following components:

- **Roles**: Collections of permissions that can be assigned to users
- **Permissions**: Granular access controls for specific actions
- **Users**: Can have multiple roles and direct permission grants/revokes

## Database Structure

### Tables

- `roles` - Stores role information
- `permissions` - Stores permission information  
- `role_user` - Many-to-many relationship between users and roles
- `permission_role` - Many-to-many relationship between roles and permissions
- `permission_user` - Direct user permissions with granted/revoked status

### Models

- `App\Models\Role` - Role model with permission relationships
- `App\Models\Permission` - Permission model with role relationships
- `App\Models\Admin` - Admin user model with role and permission methods

## Available Roles

### 1. Super Admin
- **Description**: Full access to all features
- **Use Case**: System administrators
- **Permissions**: All available permissions

### 2. Admin
- **Description**: Administrator with most access except system management
- **Use Case**: General administrators
- **Permissions**: All permissions except system management (logs, backup, maintenance)

### 3. Product Manager
- **Description**: Product-related features access
- **Use Case**: Product catalog management
- **Permissions**: Products, categories, brands, attributes, inventory management

### 4. Content Manager
- **Description**: Content and media management
- **Use Case**: Marketing content management
- **Permissions**: Banners, sliders, categories (for organization), media management

### 5. Sales Manager
- **Description**: Sales and promotional features
- **Use Case**: Sales team management
- **Permissions**: Flash sales, vouchers, orders, reports, analytics

### 6. Location Manager
- **Description**: Geographic location management
- **Use Case**: Regional management
- **Permissions**: Countries, cities, districts management

### 7. Support Staff
- **Description**: Limited access for customer support
- **Use Case**: Customer service representatives
- **Permissions**: View orders, users, products; update orders and profiles

### 8. Viewer
- **Description**: Read-only access to most features
- **Use Case**: Read-only users, observers
- **Permissions**: View permissions for most resources, profile management

## Available Permissions

### Authentication & Authorization
- `auth.login` - Can login to dashboard
- `auth.logout` - Can logout from dashboard

### User Management
- `users.view` - Can view users
- `users.create` - Can create users
- `users.update` - Can update users
- `users.delete` - Can delete users
- `users.toggle_active` - Can toggle user active status

### Role & Permission Management
- `roles.view` - Can view roles
- `roles.create` - Can create roles
- `roles.update` - Can update roles
- `roles.delete` - Can delete roles
- `roles.assign` - Can assign roles to users
- `permissions.view` - Can view permissions
- `permissions.create` - Can create permissions
- `permissions.update` - Can update permissions
- `permissions.delete` - Can delete permissions
- `permissions.assign` - Can assign permissions to roles

### Product Management
- `products.view` - Can view products
- `products.create` - Can create products
- `products.update` - Can update products
- `products.delete` - Can delete products
- `products.toggle_active` - Can toggle product active status

### Product Attributes
- `product_attributes.view` - Can view product attributes
- `product_attributes.create` - Can create product attributes
- `product_attributes.update` - Can update product attributes
- `product_attributes.delete` - Can delete product attributes
- `product_attributes.bulk_assign` - Can bulk assign attributes to products
- `product_attributes.remove_all` - Can remove all attributes from products
- `product_attributes.get_available` - Can get available attributes for products

### Product Variants
- `product_variants.view` - Can view product variants
- `product_variants.create` - Can create product variants
- `product_variants.update` - Can update product variants
- `product_variants.delete` - Can delete product variants
- `product_variants.assign_attributes` - Can assign attributes to variants
- `product_variants.remove_attributes` - Can remove attributes from variants
- `product_variants.view_with_attributes` - Can view variants with attributes

### Category Management
- `categories.view` - Can view categories
- `categories.create` - Can create categories
- `categories.update` - Can update categories
- `categories.delete` - Can delete categories
- `categories.toggle_active` - Can toggle category active status

### Brand Management
- `brands.view` - Can view brands
- `brands.create` - Can create brands
- `brands.update` - Can update brands
- `brands.delete` - Can delete brands
- `brands.toggle_active` - Can toggle brand active status

### Attribute Management
- `attributes.view` - Can view attributes
- `attributes.create` - Can create attributes
- `attributes.update` - Can update attributes
- `attributes.delete` - Can delete attributes
- `attributes.toggle_active` - Can toggle attribute active status

### Attribute Value Management
- `attribute_values.view` - Can view attribute values
- `attribute_values.create` - Can create attribute values
- `attribute_values.update` - Can update attribute values
- `attribute_values.delete` - Can delete attribute values
- `attribute_values.toggle_active` - Can toggle attribute value active status

### Banner Management
- `banners.view` - Can view banners
- `banners.create` - Can create banners
- `banners.update` - Can update banners
- `banners.delete` - Can delete banners
- `banners.toggle_active` - Can toggle banner active status

### Slider Management
- `sliders.view` - Can view sliders
- `sliders.create` - Can create sliders
- `sliders.update` - Can update sliders
- `sliders.delete` - Can delete sliders
- `sliders.toggle_active` - Can toggle slider active status

### Flash Sale Management
- `flash_sales.view` - Can view flash sales
- `flash_sales.create` - Can create flash sales
- `flash_sales.update` - Can update flash sales
- `flash_sales.delete` - Can delete flash sales
- `flash_sales.toggle_active` - Can toggle flash sale active status

### Voucher Management
- `vouchers.view` - Can view vouchers
- `vouchers.create` - Can create vouchers
- `vouchers.update` - Can update vouchers
- `vouchers.delete` - Can delete vouchers
- `vouchers.toggle_active` - Can toggle voucher active status

### Location Management
- `countries.view` - Can view countries
- `countries.create` - Can create countries
- `countries.update` - Can update countries
- `countries.delete` - Can delete countries
- `countries.toggle_active` - Can toggle country active status
- `cities.view` - Can view cities
- `cities.create` - Can create cities
- `cities.update` - Can update cities
- `cities.delete` - Can delete cities
- `cities.toggle_active` - Can toggle city active status
- `districts.view` - Can view districts
- `districts.create` - Can create districts
- `districts.update` - Can update districts
- `districts.delete` - Can delete districts
- `districts.toggle_active` - Can toggle district active status

### Order Management
- `orders.view` - Can view orders
- `orders.update` - Can update orders
- `orders.delete` - Can delete orders
- `orders.manage_status` - Can manage order status

### Inventory Management
- `inventory.view` - Can view inventory
- `inventory.update` - Can update inventory
- `inventory.manage` - Can manage inventory levels

### Media Management
- `media.upload` - Can upload media files
- `media.delete` - Can delete media files
- `media.manage` - Can manage all media files

### Reports & Analytics
- `reports.view` - Can view reports
- `reports.export` - Can export reports
- `analytics.view` - Can view analytics

### Settings Management
- `settings.view` - Can view settings
- `settings.update` - Can update settings
- `settings.manage` - Can manage all settings

### Profile Management
- `profile.view` - Can view own profile
- `profile.update` - Can update own profile
- `profile.change_password` - Can change own password

### Notifications
- `notifications.view` - Can view notifications
- `notifications.send` - Can send notifications
- `notifications.manage` - Can manage all notifications

### Dashboard Access
- `dashboard.access` - Can access dashboard
- `dashboard.overview` - Can view dashboard overview

### System Management
- `system.logs` - Can view system logs
- `system.backup` - Can manage system backups
- `system.maintenance` - Can perform system maintenance

## Usage

### Seeding Permissions and Roles

Run the seeders to create all permissions and roles:

```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
```

Or run all seeders:

```bash
php artisan db:seed
```

### Assigning Roles to Users

```php
use App\Models\Admin;

$admin = Admin::find(1);
$admin->assignRole('product_manager');
```

### Checking User Roles

```php
$admin = Admin::find(1);

// Check if user has a specific role
if ($admin->hasRole('admin')) {
    // User is an admin
}

// Check if user has any of the roles
if ($admin->hasAnyRole(['admin', 'product_manager'])) {
    // User is either admin or product manager
}

// Check if user has all roles
if ($admin->hasAllRoles(['admin', 'product_manager'])) {
    // User has both admin and product manager roles
}
```

### Checking User Permissions

```php
$admin = Admin::find(1);

// Check if user has a specific permission
if ($admin->hasPermission('products.create')) {
    // User can create products
}

// Check if user has any of the permissions
if ($admin->hasAnyPermission(['products.create', 'products.update'])) {
    // User can either create or update products
}

// Check if user has all permissions
if ($admin->hasAllPermissions(['products.create', 'products.update'])) {
    // User can both create and update products
}
```

### Direct Permission Management

```php
$admin = Admin::find(1);

// Grant permission directly to user
$admin->grantPermission('products.create');

// Revoke permission from user (overrides role permissions)
$admin->revokePermission('products.create');

// Remove direct permission (reset to role-based only)
$admin->removeDirectPermission('products.create');
```

### Using Middleware

#### CheckRole Middleware

```php
Route::middleware(['auth', 'role:admin,product_manager'])->group(function () {
    // Routes accessible by admin or product_manager
});
```

#### CheckPermission Middleware

```php
Route::middleware(['auth', 'permission:products.create'])->group(function () {
    // Routes requiring products.create permission
});

Route::middleware(['auth', 'permission:products.create|products.update'])->group(function () {
    // Routes requiring either products.create OR products.update permission
});
```

### Permission Priority

The permission system works with the following priority:

1. **Direct Permission Revoke**: If a permission is explicitly revoked from a user, it takes precedence over role permissions
2. **Direct Permission Grant**: If a permission is explicitly granted to a user, it takes precedence over role permissions
3. **Role Permissions**: If no direct permission is set, role permissions apply

## Best Practices

1. **Use Roles for Groups**: Assign permissions to roles rather than individual users
2. **Granular Permissions**: Use specific permissions rather than broad ones
3. **Principle of Least Privilege**: Give users only the minimum permissions they need
4. **Regular Audits**: Regularly review and audit user permissions and roles
5. **Documentation**: Keep documentation of role and permission changes

## Security Considerations

1. **Middleware Protection**: Always use middleware to protect routes
2. **Frontend Validation**: Remember that frontend permission checks are for UX only
3. **API Protection**: All API endpoints should be protected with appropriate middleware
4. **Audit Trail**: Consider implementing audit logs for permission changes
5. **Regular Reviews**: Periodically review user permissions and remove unnecessary access

## Troubleshooting

### Common Issues

1. **Permission Not Working**: Check if the user has the correct role assigned
2. **Role Not Assigned**: Verify the role exists and is properly assigned to the user
3. **Middleware Not Applied**: Ensure middleware is correctly applied to routes
4. **Cache Issues**: Clear application cache if permission changes aren't reflected

### Debugging

```php
// Get all user permissions
$admin = Admin::find(1);
$permissions = $admin->getAllPermissions();
dd($permissions);

// Check user roles
$roles = $admin->roles;
dd($roles);

// Check role permissions
$role = Role::where('name', 'admin')->first();
$permissions = $role->permissions;
dd($permissions);
```
