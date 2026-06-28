<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\StockLedger;
use App\Models\ImeiNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Branch $branch;
    protected Role $adminRole;
    protected ProductVariant $variant;
    protected Warehouse $warehouseA;
    protected Warehouse $warehouseB;

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

        // Catalog Setup
        $brand = Brand::create(['name' => 'Apple']);
        $category = Category::create(['name' => 'Smartphones']);
        $product = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'iPhone 15 Pro',
            'is_imei_tracked' => true,
            'status' => true
        ]);
        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'IPH15P-256',
            'cost_price' => 1000,
            'selling_price' => 1200,
            'status' => true
        ]);

        // Warehouse Setup
        $this->warehouseA = Warehouse::create([
            'name' => 'North Depot',
            'code' => 'WH-NORTH',
            'status' => true
        ]);
        $this->warehouseB = Warehouse::create([
            'name' => 'South Depot',
            'code' => 'WH-SOUTH',
            'status' => true
        ]);
    }

    public function test_admin_can_crud_warehouse(): void
    {
        // 1. Store
        $response = $this->actingAs($this->adminUser)->post('/warehouses', [
            'name' => 'East Depot',
            'code' => 'WH-EAST',
            'status' => 1
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('warehouses', ['code' => 'WH-EAST']);

        // 2. Update
        $wh = Warehouse::where('code', 'WH-EAST')->first();
        $response = $this->actingAs($this->adminUser)->put("/warehouses/{$wh->id}", [
            'name' => 'East Depot Updated',
            'code' => 'WH-EAST',
            'status' => 1
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('warehouses', ['name' => 'East Depot Updated']);

        // 3. Delete
        $response = $this->actingAs($this->adminUser)->delete("/warehouses/{$wh->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('warehouses', ['id' => $wh->id]);
    }

    public function test_admin_can_adjust_stock_with_imeis(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/inventory/adjustments', [
            'warehouse_id' => $this->warehouseA->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'quantity' => 2,
            'imeis' => ['111111111111111', '222222222222222']
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouseA->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2
        ]);
        $this->assertDatabaseHas('imei_numbers', ['imei' => '111111111111111', 'status' => 'available']);
    }

    public function test_admin_can_transfer_stock_between_warehouses(): void
    {
        // 1. Setup initial stock in Warehouse A
        $response = $this->actingAs($this->adminUser)->post('/inventory/adjustments', [
            'warehouse_id' => $this->warehouseA->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'quantity' => 2,
            'imeis' => ['111111111111111', '222222222222222']
        ]);
        $response->assertStatus(200);

        // 2. Execute Transfer to Warehouse B
        $response = $this->actingAs($this->adminUser)->post('/inventory/transfers', [
            'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseB->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
            'imeis' => ['111111111111111']
        ]);

        $response->assertStatus(200);

        // Verify counts
        $this->assertDatabaseHas('stocks', ['warehouse_id' => $this->warehouseA->id, 'quantity' => 1]);
        $this->assertDatabaseHas('stocks', ['warehouse_id' => $this->warehouseB->id, 'quantity' => 1]);

        // Verify IMEI location changed
        $this->assertDatabaseHas('imei_numbers', [
            'imei' => '111111111111111',
            'warehouse_id' => $this->warehouseB->id,
            'status' => 'available'
        ]);
    }
}
