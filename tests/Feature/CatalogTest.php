<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Color;
use App\Models\StorageVariant;
use App\Models\RamVariant;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
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

    public function test_admin_can_crud_brand(): void
    {
        // 1. Store
        $response = $this->actingAs($this->adminUser)->post('/brands', [
            'name' => 'Apple',
            'status' => 1,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('brands', ['name' => 'Apple']);

        // 2. Update
        $brand = Brand::first();
        $response = $this->actingAs($this->adminUser)->put("/brands/{$brand->id}", [
            'name' => 'Apple Inc',
            'status' => 1,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('brands', ['name' => 'Apple Inc']);

        // 3. Delete
        $response = $this->actingAs($this->adminUser)->delete("/brands/{$brand->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_admin_can_crud_category_and_subcategory(): void
    {
        // 1. Store Category
        $response = $this->actingAs($this->adminUser)->post('/categories', [
            'name' => 'Smartphones',
            'status' => 1,
        ]);
        $response->assertStatus(200);
        $category = Category::first();

        // 2. Store Subcategory
        $response = $this->actingAs($this->adminUser)->post('/subcategories', [
            'category_id' => $category->id,
            'name' => 'iPhones',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('sub_categories', ['name' => 'iPhones']);

        // 3. Fetch Subcategories via category relation endpoint
        $response = $this->actingAs($this->adminUser)->get("/categories/{$category->id}/subcategories");
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'iPhones']);
    }

    public function test_admin_can_create_product_with_variants(): void
    {
        $brand = Brand::create(['name' => 'Apple']);
        $category = Category::create(['name' => 'Smartphones']);
        $subCategory = SubCategory::create(['category_id' => $category->id, 'name' => 'iPhones']);
        
        $color = Color::create(['name' => 'Space Gray', 'code' => '808080']);
        $storage = StorageVariant::create(['value' => '256GB']);
        $ram = RamVariant::create(['value' => '8GB']);

        $response = $this->actingAs($this->adminUser)->post('/products', [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'name' => 'iPhone 15 Pro',
            'model_no' => 'A3102',
            'description' => 'Test specifications',
            'is_imei_tracked' => 1,
            'status' => 1,
            'variants' => [
                [
                    'color_id' => $color->id,
                    'storage_variant_id' => $storage->id,
                    'ram_variant_id' => $ram->id,
                    'sku' => 'IPH15P-SG-256-8',
                    'cost_price' => '999.00',
                    'selling_price' => '1199.00',
                    'alert_quantity' => 3,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['name' => 'iPhone 15 Pro']);
        $this->assertDatabaseHas('product_variants', ['sku' => 'IPH15P-SG-256-8']);
    }

    public function test_admin_can_upload_product_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $brand = Brand::create(['name' => 'Apple']);
        $category = Category::create(['name' => 'Smartphones']);
        $color = Color::create(['name' => 'Space Gray', 'code' => '808080']);
        $storage = StorageVariant::create(['value' => '256GB']);
        $ram = RamVariant::create(['value' => '8GB']);

        $file = \Illuminate\Http\UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->adminUser)->post('/products', [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'iPhone 15 Pro with Image',
            'model_no' => 'A3102',
            'is_imei_tracked' => 0,
            'status' => 1,
            'image' => $file,
            'variants' => [
                [
                    'color_id' => $color->id,
                    'storage_variant_id' => $storage->id,
                    'ram_variant_id' => $ram->id,
                    'sku' => 'IPH15P-IMG-SG-256-8',
                    'cost_price' => '999.00',
                    'selling_price' => '1199.00',
                    'alert_quantity' => 3,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $product = Product::where('name', 'iPhone 15 Pro with Image')->first();
        $this->assertNotNull($product->image);
        
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_upload_multiple_gallery_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $brand = Brand::create(['name' => 'Google']);
        $category = Category::create(['name' => 'Smartphones']);
        $color = Color::create(['name' => 'Obsidian', 'code' => '000000']);
        $storage = StorageVariant::create(['value' => '128GB']);
        $ram = RamVariant::create(['value' => '12GB']);

        $thumbnail = \Illuminate\Http\UploadedFile::fake()->image('thumbnail.jpg');
        $gallery1 = \Illuminate\Http\UploadedFile::fake()->image('gallery1.jpg');
        $gallery2 = \Illuminate\Http\UploadedFile::fake()->image('gallery2.jpg');

        $response = $this->actingAs($this->adminUser)->post('/products', [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Pixel 8 Pro Multi Image',
            'model_no' => 'GA050',
            'is_imei_tracked' => 0,
            'status' => 1,
            'image' => $thumbnail,
            'gallery_images' => [$gallery1, $gallery2],
            'variants' => [
                [
                    'color_id' => $color->id,
                    'storage_variant_id' => $storage->id,
                    'ram_variant_id' => $ram->id,
                    'sku' => 'PXL8P-OBS-128-12',
                    'cost_price' => '699.00',
                    'selling_price' => '899.00',
                    'alert_quantity' => 2,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $product = Product::where('name', 'Pixel 8 Pro Multi Image')->first();
        $this->assertNotNull($product->image);
        $this->assertCount(2, $product->images);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($product->image);
        foreach ($product->images as $img) {
            \Illuminate\Support\Facades\Storage::disk('public')->assertExists($img->image_path);
        }

        // Test Update & Delete image
        $gallery3 = \Illuminate\Http\UploadedFile::fake()->image('gallery3.jpg');
        $firstGalleryImageId = $product->images->first()->id;

        $updateResponse = $this->actingAs($this->adminUser)->put("/products/{$product->id}", [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Pixel 8 Pro Multi Image Updated',
            'model_no' => 'GA050',
            'is_imei_tracked' => 0,
            'status' => 1,
            'gallery_images' => [$gallery3],
            'delete_gallery_ids' => [$firstGalleryImageId],
            'variants' => [
                [
                    'id' => $product->variants->first()->id,
                    'color_id' => $color->id,
                    'storage_variant_id' => $storage->id,
                    'ram_variant_id' => $ram->id,
                    'sku' => 'PXL8P-OBS-128-12',
                    'cost_price' => '699.00',
                    'selling_price' => '899.00',
                    'alert_quantity' => 2,
                ]
            ]
        ]);

        $updateResponse->assertStatus(200);
        $product->refresh();
        $this->assertCount(2, $product->images); // 2 - 1 (deleted) + 1 (added) = 2
        $this->assertFalse($product->images->contains('id', $firstGalleryImageId));
    }
}
