# Account Activation Feature

## Overview
This feature implements a registration activation system where new user accounts must be activated by an administrator before they can access the system.

## Features Implemented

### 1. **User Registration Flow**
- New users register via `/register.php`
- Accounts are created with `is_active = 0` (inactive by default)
- Users see a success message indicating their account is pending activation
- No automatic login after registration

### 2. **Login Flow with Activation Check**
- When users login via `/index.php`:
  - If account is **inactive** (`is_active = 0`): Redirected to `/pending-activation.php`
  - If account is **active** (`is_active = 1`): Normal login to dashboard

### 3. **Pending Activation Page**
- Location: `/pending-activation.php`
- Shows a friendly message explaining the account is pending activation
- Provides information about what happens next
- Includes "Check Activation Status" button to refresh
- Logout option available

### 4. **Admin Activation Controls**
- Location: `/staff/list.php` (Admin only)
- Shows all users with their activation status
- Status badges:
  - **Green "Active"**: Account is activated
  - **Yellow "Pending"**: Account needs activation
- Actions available:
  - **Activate**: Sets `is_active = 1` (for pending accounts)
  - **Deactivate**: Sets `is_active = 0` (for active accounts)
  - Cannot deactivate your own account

### 5. **Admin-Only Access Control**
All admin-specific pages now require `requireAdmin()`:
- `/staff/*` - All staff management pages
- `/equipment/add.php`, `/equipment/edit.php`, `/equipment/delete.php`
- `/equipment/list.php`
- `/reports/list.php`, `/reports/damage.php`, `/reports/lost.php`
- `/logs/view.php`
- `/overdue/list.php`
- `/borrowing/edit-status.php`
- `/backup_database.php`

Staff users attempting to access admin URLs will be redirected to `/dashboard.php`.

## Database Changes

### New Column: `is_active`
```sql
ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 0 AFTER role;
```

- **Type**: TINYINT(1)
- **Default**: 0 (inactive)
- **Values**: 
  - `0` = Inactive/Pending
  - `1` = Active

## Files Created

1. **`/pending-activation.php`** - Waiting page for inactive users
2. **`/staff/activate.php`** - Admin endpoint to activate users
3. **`/staff/deactivate.php`** - Admin endpoint to deactivate users
4. **`/add_activation_column.php`** - Migration script for existing databases

## Files Modified

1. **`/setup/install.sql`** - Added `is_active` column to users table
2. **`/register.php`** - Creates inactive accounts, shows pending message
3. **`/index.php`** - Checks activation status on login
4. **`/staff/list.php`** - Shows status column and activation controls
5. **`/staff/add.php`** - Admin-created users are active by default
6. **`/equipment/add.php`** - Added `requireAdmin()`
7. **`/equipment/edit.php`** - Added `requireAdmin()`
8. **`/equipment/delete.php`** - Changed to `requireAdmin()`
9. **`/reports/damage.php`** - Added `requireAdmin()`
10. **`/reports/lost.php`** - Added `requireAdmin()`

## Installation Instructions

### For New Installations
1. Run the updated `/setup/install.sql` - it includes the `is_active` column

### For Existing Databases
1. Run `/add_activation_column.php` in your browser
2. This will:
   - Add the `is_active` column to the users table
   - Set all existing users to active (`is_active = 1`)
3. Delete the file after running for security

## Usage

### As a New User
1. Register at `/register.php`
2. See success message about pending activation
3. Wait for admin to activate your account
4. Try logging in - you'll see the pending activation page
5. Once activated, login normally

### As an Administrator
1. Go to **Staff Management** (`/staff/list.php`)
2. View all users with their activation status
3. Click **Activate** next to pending accounts
4. Click **Deactivate** to suspend active accounts
5. Users created by admin are automatically active

## Security Features

- ✅ Admin-only pages protected with `requireAdmin()`
- ✅ Staff cannot access admin URLs
- ✅ Admins cannot deactivate their own account
- ✅ Inactive users cannot access the system
- ✅ Session-based authentication maintained
- ✅ All user inputs sanitized and validated

## Testing Checklist

- [ ] Register a new account - should be inactive
- [ ] Login with inactive account - should see pending page
- [ ] Admin activates the account
- [ ] Login again - should access dashboard normally
- [ ] Admin deactivates an active account
- [ ] That user tries to login - should see pending page
- [ ] Staff user tries to access `/staff/list.php` - should redirect
- [ ] Admin creates new user - should be active by default
- [ ] Try to deactivate own admin account - should fail

## Notes

- All existing users are set to **active** by default during migration
- New registrations are **inactive** by default
- Admin-created users are **active** by default
- The `requireAdmin()` function is defined in `/config/database.php`
