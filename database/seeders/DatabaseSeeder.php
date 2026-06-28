<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Branch
        $branch = Branch::create([
            'name' => 'Headquarters',
            'code' => 'HQ001',
            'phone' => '+1234567890',
            'email' => 'hq@mobileshop.com',
            'address' => '123 Main Street, Tech City',
            'status' => true,
        ]);

        // 2. Define Roles
        $rolesData = [
            ['name' => 'super-admin', 'display_name' => 'Super Admin', 'description' => 'Has absolute control over the ERP system.'],
            ['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Manages branch operations, settings, and users.'],
            ['name' => 'manager', 'display_name' => 'Manager', 'description' => 'Oversees inventory, purchases, sales, and accounts.'],
            ['name' => 'sales-executive', 'display_name' => 'Sales Executive', 'description' => 'Handles sales entries and quotations.'],
            ['name' => 'cashier', 'display_name' => 'Cashier', 'description' => 'Manages POS billing and receipts.'],
            ['name' => 'purchase-executive', 'display_name' => 'Purchase Executive', 'description' => 'Manages suppliers, enquiries, and purchase orders.'],
            ['name' => 'warehouse', 'display_name' => 'Warehouse Manager', 'description' => 'Manages stock intake, GRNs, and stock transfers.'],
            ['name' => 'accountant', 'display_name' => 'Accountant', 'description' => 'Manages books of accounts, ledger postings, and financial reports.'],
            ['name' => 'staff', 'display_name' => 'Staff', 'description' => 'General shop staff helper.'],
            ['name' => 'customer', 'display_name' => 'Customer', 'description' => 'Retail / wholesale customer with portal access.']
        ];

        $roles = [];
        foreach ($rolesData as $roleInfo) {
            $roles[$roleInfo['name']] = Role::create($roleInfo);
        }

        // 3. Define Permissions (Menu-wise and Button-wise)
        $permissionsData = [
            // Dashboard & Users
            ['name' => 'view-dashboard', 'display_name' => 'View Dashboard', 'module' => 'Dashboard', 'type' => 'menu'],
            ['name' => 'view-users', 'display_name' => 'View Users List', 'module' => 'Users', 'type' => 'menu'],
            ['name' => 'create-users', 'display_name' => 'Create Users', 'module' => 'Users', 'type' => 'button'],
            ['name' => 'edit-users', 'display_name' => 'Edit Users', 'module' => 'Users', 'type' => 'button'],
            ['name' => 'delete-users', 'display_name' => 'Delete Users', 'module' => 'Users', 'type' => 'button'],
            
            // Roles & Permissions
            ['name' => 'view-roles', 'display_name' => 'View Roles', 'module' => 'Roles', 'type' => 'menu'],
            ['name' => 'manage-roles', 'display_name' => 'Manage Roles & Permissions', 'module' => 'Roles', 'type' => 'button'],
            
            // Catalog
            ['name' => 'view-products', 'display_name' => 'View Products', 'module' => 'Catalog', 'type' => 'menu'],
            ['name' => 'create-products', 'display_name' => 'Create Products', 'module' => 'Catalog', 'type' => 'button'],
            ['name' => 'edit-products', 'display_name' => 'Edit Products', 'module' => 'Catalog', 'type' => 'button'],
            ['name' => 'delete-products', 'display_name' => 'Delete Products', 'module' => 'Catalog', 'type' => 'button'],
            ['name' => 'import-products', 'display_name' => 'Import Products', 'module' => 'Catalog', 'type' => 'button'],
            ['name' => 'export-products', 'display_name' => 'Export Products', 'module' => 'Catalog', 'type' => 'button'],

            // Contacts
            ['name' => 'view-contacts', 'display_name' => 'View Contacts Menu', 'module' => 'Contacts', 'type' => 'menu'],
            ['name' => 'manage-contacts', 'display_name' => 'Manage Customers & Suppliers', 'module' => 'Contacts', 'type' => 'button'],

            // Inventory
            ['name' => 'view-inventory', 'display_name' => 'View Inventory List', 'module' => 'Inventory', 'type' => 'menu'],
            ['name' => 'stock-transfer', 'display_name' => 'Transfer Stock', 'module' => 'Inventory', 'type' => 'button'],
            ['name' => 'stock-adjust', 'display_name' => 'Adjust Stock', 'module' => 'Inventory', 'type' => 'button'],

            // Purchase
            ['name' => 'view-purchases', 'display_name' => 'View Purchases', 'module' => 'Purchase', 'type' => 'menu'],
            ['name' => 'create-purchases', 'display_name' => 'Create Purchase Orders', 'module' => 'Purchase', 'type' => 'button'],
            ['name' => 'approve-po', 'display_name' => 'Approve Purchase Orders', 'module' => 'Purchase', 'type' => 'button'],
            ['name' => 'manage-grn', 'display_name' => 'Receive Goods (GRN)', 'module' => 'Purchase', 'type' => 'button'],

            // Sales & POS
            ['name' => 'view-sales', 'display_name' => 'View Sales Records', 'module' => 'Sales', 'type' => 'menu'],
            ['name' => 'pos-billing', 'display_name' => 'Access POS Billing', 'module' => 'Sales', 'type' => 'menu'],
            ['name' => 'create-sales', 'display_name' => 'Create Sales Invoices', 'module' => 'Sales', 'type' => 'button'],
            ['name' => 'approve-sales-return', 'display_name' => 'Approve Sales Return/Exchange', 'module' => 'Sales', 'type' => 'button'],

            // Accounts
            ['name' => 'view-accounts', 'display_name' => 'View Accounts Module', 'module' => 'Accounts', 'type' => 'menu'],
            ['name' => 'manage-accounts', 'display_name' => 'Manage Accounting Vouchers', 'module' => 'Accounts', 'type' => 'button'],
            ['name' => 'view-financial-reports', 'display_name' => 'View Financial Reports', 'module' => 'Accounts', 'type' => 'button'],

            // Settings
            ['name' => 'view-settings', 'display_name' => 'View Settings Panel', 'module' => 'Settings', 'type' => 'menu'],
            ['name' => 'edit-settings', 'display_name' => 'Modify Settings', 'module' => 'Settings', 'type' => 'button'],
        ];

        $permissions = [];
        foreach ($permissionsData as $permInfo) {
            $permissions[$permInfo['name']] = Permission::create($permInfo);
        }

        // 4. Assign Permissions to Roles
        // Super Admin gets everything (handled via Gate::before anyway, but let's link them explicitly for pivot visibility)
        $roles['super-admin']->permissions()->sync(Permission::all());

        // Admin Permissions (everything except manage system databases directly)
        $adminPerms = Permission::whereIn('module', ['Dashboard', 'Users', 'Roles', 'Catalog', 'Contacts', 'Inventory', 'Purchase', 'Sales', 'Accounts', 'Settings'])->get();
        $roles['admin']->permissions()->sync($adminPerms);

        // Manager Permissions
        $managerPerms = Permission::whereIn('name', [
            'view-dashboard', 'view-products', 'create-products', 'edit-products', 'view-contacts', 'manage-contacts', 
            'view-inventory', 'stock-transfer', 'stock-adjust', 'view-purchases', 'create-purchases', 'manage-grn', 
            'view-sales', 'pos-billing', 'create-sales', 'approve-sales-return', 'view-accounts', 'view-financial-reports'
        ])->get();
        $roles['manager']->permissions()->sync($managerPerms);

        // Cashier Permissions
        $cashierPerms = Permission::whereIn('name', ['view-dashboard', 'pos-billing', 'view-products', 'view-sales', 'create-sales'])->get();
        $roles['cashier']->permissions()->sync($cashierPerms);

        // Sales Executive Permissions
        $salesPerms = Permission::whereIn('name', ['view-dashboard', 'pos-billing', 'view-products', 'view-sales', 'create-sales'])->get();
        $roles['sales-executive']->permissions()->sync($salesPerms);

        // Purchase Executive Permissions
        $purchasePerms = Permission::whereIn('name', ['view-dashboard', 'view-products', 'view-contacts', 'manage-contacts', 'view-purchases', 'create-purchases'])->get();
        $roles['purchase-executive']->permissions()->sync($purchasePerms);

        // Accountant Permissions
        $accountantPerms = Permission::whereIn('name', ['view-dashboard', 'view-accounts', 'manage-accounts', 'view-financial-reports', 'view-sales', 'view-purchases'])->get();
        $roles['accountant']->permissions()->sync($accountantPerms);

        // 5. Create Seed Users
        $usersData = [
            [
                'branch_id' => $branch->id,
                'name' => 'Super Admin User',
                'username' => 'superadmin',
                'email' => 'superadmin@mobileshop.com',
                'phone' => '+1000000001',
                'password' => Hash::make('password'),
                'status' => true,
                'role' => 'super-admin',
            ],
            [
                'branch_id' => $branch->id,
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@mobileshop.com',
                'phone' => '+1000000002',
                'password' => Hash::make('password'),
                'status' => true,
                'role' => 'admin',
            ],
            [
                'branch_id' => $branch->id,
                'name' => 'Manager User',
                'username' => 'manager',
                'email' => 'manager@mobileshop.com',
                'phone' => '+1000000003',
                'password' => Hash::make('password'),
                'status' => true,
                'role' => 'manager',
            ],
            [
                'branch_id' => $branch->id,
                'name' => 'Cashier User',
                'username' => 'cashier',
                'email' => 'cashier@mobileshop.com',
                'phone' => '+1000000004',
                'password' => Hash::make('password'),
                'status' => true,
                'role' => 'cashier',
            ],
            [
                'branch_id' => $branch->id,
                'name' => 'Sales Exec User',
                'username' => 'salesexec',
                'email' => 'salesexec@mobileshop.com',
                'phone' => '+1000000005',
                'password' => Hash::make('password'),
                'status' => true,
                'role' => 'sales-executive',
            ],
            [
                'branch_id' => $branch->id,
                'name' => 'Accountant User',
                'username' => 'accountant',
                'email' => 'accountant@mobileshop.com',
                'phone' => '+1000000006',
                'password' => Hash::make('password'),
                'status' => true,
                'role' => 'accountant',
            ]
        ];

        foreach ($usersData as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            
            $user = User::create($userData);
            $user->roles()->attach($roles[$roleName]->id);
        }

        // 6. Seed Catalog and Inventory modules
        $this->call([
            CatalogAndInventorySeeder::class,
        ]);
    }
}
