# Permission Revocation with Role Cleanup Example

This document explains how the permission revocation system works with automatic role cleanup.

## How It Works

When you revoke a permission from an admin, the system:

1. **Checks for roles containing the permission** - Finds all roles assigned to the admin that contain the permission being revoked
2. **Removes those roles** - Automatically removes all roles that contain the permission
3. **Revokes the permission** - Sets the permission as explicitly revoked
4. **Cleans up database** - Removes the role assignments from the database

## Example Scenario

### Initial State
```
Admin: John Doe
Roles: [Content Manager, Product Manager]
Permissions: 
- manage_products (from Product Manager role)
- create_content (from Content Manager role)
- edit_content (from Content Manager role)
```

### Revoke Permission: manage_products

**What happens:**
1. System finds that "Product Manager" role contains "manage_products" permission
2. System removes "Product Manager" role from admin
3. System revokes "manage_products" permission directly
4. Database cleanup removes the role assignment

**Final State:**
```
Admin: John Doe
Roles: [Content Manager]  // Product Manager role removed
Permissions:
- create_content (from Content Manager role)
- edit_content (from Content Manager role)
- manage_products (explicitly revoked - cannot access)
```

## API Usage

### Revoke Permission Endpoint
```http
DELETE /api/dashboard/admins/{admin}/revoke-permission
Content-Type: application/json
Authorization: Bearer {token}

{
    "permission_id": 5
}
```

### Response
```json
{
    "success": true,
    "message": "Permission revoked successfully and 1 related role(s) removed",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "roles": [
            {
                "id": 2,
                "name": "content_manager",
                "description": "Manages content"
            }
        ],
        "permissions": [
            {
                "id": 3,
                "name": "create_content",
                "pivot": {
                    "granted": true
                }
            },
            {
                "id": 4,
                "name": "edit_content",
                "pivot": {
                    "granted": true
                }
            },
            {
                "id": 5,
                "name": "manage_products",
                "pivot": {
                    "granted": false
                }
            }
        ]
    }
}
```

## Database Changes

### Before Revocation
```sql
-- role_user table
user_id | role_id
1       | 2      -- Content Manager
1       | 3      -- Product Manager

-- permission_user table
user_id | permission_id | granted
1       | 3            | true    -- create_content
1       | 4            | true    -- edit_content
1       | 5            | true    -- manage_products (from role)
```

### After Revocation
```sql
-- role_user table
user_id | role_id
1       | 2      -- Content Manager (Product Manager removed)

-- permission_user table
user_id | permission_id | granted
1       | 3            | true     -- create_content
1       | 4            | true     -- edit_content
1       | 5            | false    -- manage_products (explicitly revoked)
```

## Observer Pattern

The system uses observers to handle cleanup:

### AdminRoleObserver
- Triggers when roles are removed from admins
- Cleans up any direct permissions that were granted through the removed role
- Logs all role changes for audit purposes

### AdminPermissionObserver
- Triggers when permissions are revoked
- Handles the role removal logic
- Logs permission changes for audit purposes

## Security Benefits

1. **Consistent State** - Ensures admin cannot access permission through any role after revocation
2. **Audit Trail** - All changes are logged for security auditing
3. **Automatic Cleanup** - No orphaned role assignments or permissions
4. **Explicit Revocation** - Permission is explicitly revoked, not just removed from roles

## Error Handling

The system handles various error scenarios:

1. **Permission not found** - Returns 404 error
2. **Admin not found** - Returns 404 error
3. **Database errors** - Logs error and returns server error
4. **Role removal fails** - Continues with permission revocation and logs error

## Testing the Feature

### Test Case 1: Revoke Permission from Role
```bash
# 1. Create admin with role
POST /api/dashboard/admins
{
    "name": "Test Admin",
    "email": "test@example.com",
    "roles": [2]  // Product Manager role
}

# 2. Revoke permission that exists in the role
DELETE /api/dashboard/admins/1/revoke-permission
{
    "permission_id": 5  // manage_products
}

# 3. Check that role was removed
GET /api/dashboard/admins/1
```

### Test Case 2: Revoke Permission Not in Roles
```bash
# 1. Grant direct permission
POST /api/dashboard/admins/1/grant-permission
{
    "permission_id": 6  // special_permission
}

# 2. Revoke the direct permission
DELETE /api/dashboard/admins/1/revoke-permission
{
    "permission_id": 6
}

# 3. Check that only permission was revoked (no roles affected)
GET /api/dashboard/admins/1
```

## Logging

All permission and role changes are logged:

```
[2024-01-15 10:30:00] INFO: Permission 'manage_products' revoked from admin 'John Doe'
[2024-01-15 10:30:00] INFO: Role 'Product Manager' removed from admin 'John Doe' because it contained revoked permission 'manage_products'
```

This ensures complete audit trail for security compliance.
