<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Branch $branch;
    protected Role $adminRole;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'HQ Branch',
            'code' => 'HQ001',
            'status' => true,
        ]);

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
    }

    public function test_homepage_loads_and_contains_hero(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Next-Gen');
        $response->assertSee('Devices');
        $response->assertSee('iPhone 15 Pro');
    }

    public function test_shop_page_loads_with_filters(): void
    {
        $response = $this->get('/shop');

        $response->assertStatus(200);
        $response->assertSee('Filters');
        $response->assertSee('Apple');
    }

    public function test_shop_ajax_filtering_returns_product_grid(): void
    {
        $response = $this->get('/shop', ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertSee('iPhone 15 Pro');
    }
}
