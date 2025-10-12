# Admin Management System Guide

This guide explains how to use the admin management system that has been implemented in the dashboard.

## Overview

The admin management system provides comprehensive CRUD operations for:
- **Admins**: Create, read, update, delete admin users
- **Roles**: Manage roles and assign permissions to them
- **Permissions**: Create and manage individual permissions
- **Role Assignment**: Assign/remove roles from admins
- **Permission Management**: Grant/revoke permissions directly to admins

## API Endpoints

### Admin Management

#### Basic CRUD Operations
- `GET /api/dashboard/admins` - List all admins
- `POST /api/dashboard/admins` - Create new admin
- `GET /api/dashboard/admins/{admin}` - Get specific admin
- `PUT /api/dashboard/admins/{admin}` - Update admin
- `DELETE /api/dashboard/admins/{admin}` - Delete admin

#### Role Management for Admins
- `POST /api/dashboard/admins/{admin}/assign-role` - Assign role to admin
- `DELETE /api/dashboard/admins/{admin}/remove-role` - Remove role from admin

#### Permission Management for Admins
- `POST /api/dashboard/admins/{admin}/grant-permission` - Grant permission to admin
- `DELETE /api/dashboard/admins/{admin}/revoke-permission` - Revoke permission from admin
- `DELETE /api/dashboard/admins/{admin}/remove-direct-permission` - Remove direct permission

#### Utility Endpoints
- `GET /api/dashboard/available-roles` - Get all available roles
- `GET /api/dashboard/available-permissions` - Get all available permissions

### Role Management

#### Basic CRUD Operations
- `GET /api/dashboard/roles` - List all roles
- `POST /api/dashboard/roles` - Create new role
- `GET /api/dashboard/roles/{role}` - Get specific role
- `PUT /api/dashboard/roles/{role}` - Update role
- `DELETE /api/dashboard/roles/{role}` - Delete role

#### Permission Management for Roles
- `POST /api/dashboard/roles/{role}/assign-permission` - Assign permission to role
- `DELETE /api/dashboard/roles/{role}/remove-permission` - Remove permission from role
- `GET /api/dashboard/roles/{role}/available-permissions` - Get available permissions for role

### Permission Management

#### Basic CRUD Operations
- `GET /api/dashboard/permissions` - List all permissions
- `POST /api/dashboard/permissions` - Create new permission
- `GET /api/dashboard/permissions/{permission}` - Get specific permission
- `PUT /api/dashboard/permissions/{permission}` - Update permission
- `DELETE /api/dashboard/permissions/{permission}` - Delete permission

## Request/Response Examples

### Create Admin
```json
POST /api/dashboard/admins
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123",
    "locale": "en",
    "notification_status": true,
    "roles": [1, 2],
    "permissions": [3, 4]
}
```

### Assign Role to Admin
```json
POST /api/dashboard/admins/1/assign-role
{
    "role_id": 2
}
```

### Grant Permission to Admin
```json
POST /api/dashboard/admins/1/grant-permission
{
    "permission_id": 5
}
```

### Create Role
```json
POST /api/dashboard/roles
{
    "name": "content_manager",
    "description": "Manages content and media",
    "permissions": [1, 2, 3]
}
```

### Create Permission
```json
POST /api/dashboard/permissions
{
    "name": "manage_products",
    "description": "Can create, update, and delete products"
}
```

## Permission System Logic

### Permission Hierarchy
1. **Role-based permissions**: Admins inherit permissions from their roles
2. **Direct permissions**: Can be granted or revoked directly to admins
3. **Permission precedence**: Direct permissions override role permissions

### Permission States
- **Granted**: Permission is explicitly granted to the admin
- **Revoked**: Permission is explicitly revoked from the admin
- **Inherited**: Permission comes from the admin's roles
- **Not assigned**: Permission is neither granted nor inherited

### Security Features
- Only super admins can manage other admins
- Cannot delete the last super admin
- Cannot delete super_admin role
- Cannot delete roles/permissions that are in use
- Password hashing is automatic
- Email and phone uniqueness validation

## Middleware Protection

All admin management endpoints are protected by:
- `role:super_admin` - Only super admins can access these endpoints

## Error Handling

The system provides comprehensive error handling:
- Validation errors with detailed messages
- Business logic errors (e.g., cannot delete last super admin)
- Server errors with appropriate HTTP status codes

## Usage Examples

### 1. Create a New Admin with Roles and Permissions
```bash
curl -X POST /api/dashboard/admins \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1987654321",
    "password": "securepassword",
    "password_confirmation": "securepassword",
    "roles": [1],
    "permissions": [5, 6]
  }'
```

### 2. Assign a Role to an Admin
```bash
curl -X POST /api/dashboard/admins/1/assign-role \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"role_id": 2}'
```

### 3. Grant a Permission to an Admin
```bash
curl -X POST /api/dashboard/admins/1/grant-permission \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"permission_id": 7}'
```

### 4. Create a New Role
```bash
curl -X POST /api/dashboard/roles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "inventory_manager",
    "description": "Manages inventory and stock",
    "permissions": [8, 9, 10]
  }'
```

## Database Relationships

- `users` table stores admin information
- `roles` table stores role information
- `permissions` table stores permission information
- `role_user` table links admins to roles
- `permission_user` table links admins to permissions with granted status
- `permission_role` table links roles to permissions

## Best Practices

1. **Role Design**: Create roles based on job functions (e.g., content_manager, inventory_manager)
2. **Permission Granularity**: Create specific permissions for each action
3. **Least Privilege**: Only grant necessary permissions
4. **Regular Audits**: Periodically review admin permissions
5. **Strong Passwords**: Enforce strong password policies
6. **Monitor Access**: Log admin actions for security auditing

## Troubleshooting

### Common Issues
1. **Cannot delete admin**: Check if it's the last super admin
2. **Cannot delete role**: Check if role is assigned to any admins
3. **Cannot delete permission**: Check if permission is assigned to roles or admins
4. **Validation errors**: Check request format and required fields
5. **Authorization errors**: Ensure user has super_admin role

### Debug Tips
- Check the response messages for specific error details
- Verify the admin has the super_admin role
- Ensure all required fields are provided
- Check for unique constraint violations (email, phone)
