<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessConfigTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Branch $branch;
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'HQ Branch',
            'code' => 'HQ001',
            'status' => true,
        ]);

        $this->adminRole = Role::create([
            'name' => 'super-admin',
            'display_name' => 'Super Admin',
        ]);

        $this->adminUser = User::create([
            'branch_id' => $this->branch->id,
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);

        $this->adminUser->roles()->attach($this->adminRole->id);
    }

    public function test_admin_can_crud_customer(): void
    {
        // 1. Store
        $response = $this->actingAs($this->adminUser)->post('/customers', [
            'name' => 'John Doe',
            'phone' => '+15550199',
            'email' => 'john@example.com',
            'address' => '123 Elm Street',
            'status' => 1,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', ['name' => 'John Doe']);

        // 2. Update
        $customer = Customer::first();
        $response = $this->actingAs($this->adminUser)->put("/customers/{$customer->id}", [
            'name' => 'John Doe Updated',
            'phone' => '+15550199',
            'email' => 'john.updated@example.com',
            'address' => '123 Elm Street',
            'status' => 1,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', ['name' => 'John Doe Updated']);

        // 3. Delete
        $response = $this->actingAs($this->adminUser)->delete("/customers/{$customer->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_admin_can_crud_supplier(): void
    {
        // 1. Store
        $response = $this->actingAs($this->adminUser)->post('/suppliers', [
            'name' => 'Apple Wholesale',
            'contact_person' => 'Tim Cook',
            'phone' => '+15550188',
            'email' => 'tim@apple.com',
            'address' => 'Infinite Loop, Cupertino',
            'gstin' => 'GSTIN999999',
            'status' => 1,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('suppliers', ['name' => 'Apple Wholesale']);

        // 2. Update
        $supplier = Supplier::first();
        $response = $this->actingAs($this->adminUser)->put("/suppliers/{$supplier->id}", [
            'name' => 'Apple Corp',
            'contact_person' => 'Tim Cook',
            'phone' => '+15550188',
            'email' => 'tim@apple.com',
            'address' => 'Infinite Loop, Cupertino',
            'gstin' => 'GSTIN999999',
            'status' => 1,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('suppliers', ['name' => 'Apple Corp']);

        // 3. Delete
        $response = $this->actingAs($this->adminUser)->delete("/suppliers/{$supplier->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_admin_can_update_company_settings(): void
    {
        // Setup initial company profile
        $company = Company::create([
            'name' => 'Default Mobile',
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);

        $response = $this->actingAs($this->adminUser)->put('/settings', [
            'name' => 'My Premium Mobile Shop',
            'email' => 'premium@mobileshop.com',
            'phone' => '+15550000',
            'website' => 'www.premiumshop.com',
            'address' => 'Main Ave',
            'tax_number' => 'GSTIN8888',
            'currency' => 'EUR',
            'currency_symbol' => '€',
        ]);

        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseHas('companies', ['name' => 'My Premium Mobile Shop', 'currency' => 'EUR']);
    }
}
