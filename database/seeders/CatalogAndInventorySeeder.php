<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Color;
use App\Models\StorageVariant;
use App\Models\RamVariant;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\StockLedger;
use App\Models\ImeiNumber;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\PaymentMode;
use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogAndInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Brands
            $brands = [
                'Apple' => Brand::create(['name' => 'Apple', 'status' => true]),
                'Samsung' => Brand::create(['name' => 'Samsung', 'status' => true]),
                'Xiaomi' => Brand::create(['name' => 'Xiaomi', 'status' => true]),
                'OnePlus' => Brand::create(['name' => 'OnePlus', 'status' => true]),
                'Google' => Brand::create(['name' => 'Google', 'status' => true]),
                'Nothing' => Brand::create(['name' => 'Nothing', 'status' => true]),
            ];

            // 2. Categories
            $categories = [
                'Phones' => Category::create(['name' => 'Smartphones', 'status' => true]),
                'Accessories' => Category::create(['name' => 'Accessories', 'status' => true]),
            ];

            // 3. Subcategories
            $subCategories = [
                'iPhones' => SubCategory::create(['category_id' => $categories['Phones']->id, 'name' => 'iPhones', 'status' => true]),
                'Galaxy' => SubCategory::create(['category_id' => $categories['Phones']->id, 'name' => 'Galaxy Phones', 'status' => true]),
                'Pixel' => SubCategory::create(['category_id' => $categories['Phones']->id, 'name' => 'Pixel Phones', 'status' => true]),
                'OnePlusSub' => SubCategory::create(['category_id' => $categories['Phones']->id, 'name' => 'OnePlus Devices', 'status' => true]),
                'Chargers' => SubCategory::create(['category_id' => $categories['Accessories']->id, 'name' => 'Chargers', 'status' => true]),
                'Earbuds' => SubCategory::create(['category_id' => $categories['Accessories']->id, 'name' => 'Earbuds', 'status' => true]),
            ];

            // 4. Colors
            $colors = [
                'Black' => Color::create(['name' => 'Midnight Black', 'code' => '1c1d21']),
                'Gray' => Color::create(['name' => 'Space Gray', 'code' => '555555']),
                'Silver' => Color::create(['name' => 'Titanium Silver', 'code' => 'd3d3d3']),
                'Green' => Color::create(['name' => 'Emerald Green', 'code' => '0f5132']),
                'White' => Color::create(['name' => 'Snow White', 'code' => 'ffffff']),
                'Gold' => Color::create(['name' => 'Titanium Gold', 'code' => 'd4af37']),
            ];

            // 5. Storage capacity
            $storage = [
                '128G' => StorageVariant::create(['value' => '128GB']),
                '256G' => StorageVariant::create(['value' => '256GB']),
                '512G' => StorageVariant::create(['value' => '512GB']),
                '1TB' => StorageVariant::create(['value' => '1TB']),
            ];

            // 6. RAM capacity
            $ram = [
                '8G' => RamVariant::create(['value' => '8GB']),
                '12G' => RamVariant::create(['value' => '12GB']),
                '16G' => RamVariant::create(['value' => '16GB']),
                '18G' => RamVariant::create(['value' => '18GB']),
            ];

            // 7. Warehouses
            $warehouses = [
                'North' => Warehouse::create(['name' => 'North Outlet Depot', 'code' => 'WH-NORTH', 'status' => true]),
                'South' => Warehouse::create(['name' => 'South Depot Terminal', 'code' => 'WH-SOUTH', 'status' => true]),
            ];

            // 8. Retail Customers
            Customer::create([
                'name' => 'Walk-in Guest',
                'phone' => '1000000000',
                'email' => 'guest@mobileshop.com',
                'address' => 'Counter Registry',
                'wallet_balance' => 0.00,
                'loyalty_points' => 0,
                'status' => true
            ]);
            Customer::create([
                'name' => 'Alice Johnson',
                'phone' => '9876543210',
                'email' => 'alice@gmail.com',
                'address' => '789 Oak Lane, Tech City',
                'wallet_balance' => 50.00,
                'loyalty_points' => 150,
                'status' => true
            ]);

            // 9. Suppliers
            Supplier::create([
                'name' => 'Apple Regional Wholesale',
                'contact_person' => 'Tim Cook',
                'phone' => '180027753',
                'email' => 'distributor@apple.com',
                'address' => 'Cupertino Loop, CA',
                'gstin' => '07APPLE1234F1Z1',
                'outstanding_balance' => 0.00,
                'status' => true
            ]);
            Supplier::create([
                'name' => 'Samsung Corp Distributors',
                'contact_person' => 'Lee Jae-yong',
                'phone' => '180055533',
                'email' => 'supply@samsung.com',
                'address' => 'Suwon Hub, South Korea',
                'gstin' => '07SAMSU5678F1Z2',
                'outstanding_balance' => 4500.00,
                'status' => true
            ]);

            // 10. Payment Modes
            PaymentMode::create(['name' => 'Cash', 'status' => true]);
            PaymentMode::create(['name' => 'Card Payment', 'status' => true]);
            PaymentMode::create(['name' => 'UPI Scan', 'status' => true]);
            PaymentMode::create(['name' => 'Bank Transfer', 'status' => true]);

            // 11. Discount Coupons
            Coupon::create([
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_cart_amount' => 100.00,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
                'status' => true
            ]);
            Coupon::create([
                'code' => 'FLAT50',
                'type' => 'fixed',
                'value' => 50.00,
                'min_cart_amount' => 300.00,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(15),
                'status' => true
            ]);

            // 12. Products & Variants Setup
            // A. Tracked by IMEI: iPhone 15 Pro
            $iphone = Product::create([
                'brand_id' => $brands['Apple']->id,
                'category_id' => $categories['Phones']->id,
                'sub_category_id' => $subCategories['iPhones']->id,
                'name' => 'iPhone 15 Pro',
                'model_no' => 'A3102',
                'description' => 'Super Retina XDR, Dynamic Island, A17 Pro Chip.',
                'is_imei_tracked' => true,
                'status' => true
            ]);

            $iphoneVar1 = ProductVariant::create([
                'product_id' => $iphone->id,
                'color_id' => $colors['Gray']->id,
                'storage_variant_id' => $storage['256G']->id,
                'ram_variant_id' => $ram['8G']->id,
                'sku' => 'IPH15P-SG-256-8',
                'cost_price' => 999.00,
                'selling_price' => 1199.00,
                'alert_quantity' => 5,
                'status' => true
            ]);

            // Seed Initial Stock in Warehouse North (Quantity: 3)
            Stock::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $iphoneVar1->id,
                'quantity' => 3
            ]);

            StockLedger::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $iphoneVar1->id,
                'type' => 'in',
                'quantity' => 3,
                'reference_type' => 'Opening Stock',
                'reference_id' => null,
                'old_quantity' => 0,
                'new_quantity' => 3,
                'user_id' => 1, // Super admin
                'created_at' => now(),
            ]);

            // Create IMEIs for it
            for ($i = 1; $i <= 3; $i++) {
                ImeiNumber::create([
                    'product_variant_id' => $iphoneVar1->id,
                    'warehouse_id' => $warehouses['North']->id,
                    'imei' => '35948210000000' . $i,
                    'status' => 'available'
                ]);
            }

            // B. Tracked by IMEI: Galaxy S24 Ultra
            $samsung = Product::create([
                'brand_id' => $brands['Samsung']->id,
                'category_id' => $categories['Phones']->id,
                'sub_category_id' => $subCategories['Galaxy']->id,
                'name' => 'Galaxy S24 Ultra',
                'model_no' => 'SM-S928B',
                'description' => 'Titanium frame, Quad Telephoto Camera, Snapdragon 8 Gen 3.',
                'is_imei_tracked' => true,
                'status' => true
            ]);

            $samsungVar1 = ProductVariant::create([
                'product_id' => $samsung->id,
                'color_id' => $colors['Black']->id,
                'storage_variant_id' => $storage['512G']->id,
                'ram_variant_id' => $ram['12G']->id,
                'sku' => 'S24U-MB-512-12',
                'cost_price' => 1099.00,
                'selling_price' => 1299.00,
                'alert_quantity' => 4,
                'status' => true
            ]);

            // Seed Initial Stock in Warehouse South (Quantity: 2)
            Stock::create([
                'warehouse_id' => $warehouses['South']->id,
                'product_variant_id' => $samsungVar1->id,
                'quantity' => 2
            ]);

            StockLedger::create([
                'warehouse_id' => $warehouses['South']->id,
                'product_variant_id' => $samsungVar1->id,
                'type' => 'in',
                'quantity' => 2,
                'reference_type' => 'Opening Stock',
                'reference_id' => null,
                'old_quantity' => 0,
                'new_quantity' => 2,
                'user_id' => 1,
                'created_at' => now(),
            ]);

            // Create IMEIs for Samsung S24
            for ($i = 1; $i <= 2; $i++) {
                ImeiNumber::create([
                    'product_variant_id' => $samsungVar1->id,
                    'warehouse_id' => $warehouses['South']->id,
                    'imei' => '35820921000000' . $i,
                    'status' => 'available'
                ]);
            }

            // C. Tracked by IMEI: Google Pixel 8 Pro
            $pixel = Product::create([
                'brand_id' => $brands['Google']->id,
                'category_id' => $categories['Phones']->id,
                'sub_category_id' => $subCategories['Pixel']->id,
                'name' => 'Pixel 8 Pro',
                'model_no' => 'GC3VE',
                'description' => 'Google Tensor G3, Pro triple camera system, Obsidian finish.',
                'is_imei_tracked' => true,
                'status' => true
            ]);

            $pixelVar = ProductVariant::create([
                'product_id' => $pixel->id,
                'color_id' => $colors['Black']->id,
                'storage_variant_id' => $storage['128G']->id,
                'ram_variant_id' => $ram['12G']->id,
                'sku' => 'PIX8P-OB-128-12',
                'cost_price' => 649.00,
                'selling_price' => 799.00,
                'alert_quantity' => 3,
                'status' => true
            ]);

            // Seed stock in Warehouse North (Quantity: 2)
            Stock::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $pixelVar->id,
                'quantity' => 2
            ]);

            StockLedger::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $pixelVar->id,
                'type' => 'in',
                'quantity' => 2,
                'reference_type' => 'Opening Stock',
                'old_quantity' => 0,
                'new_quantity' => 2,
                'user_id' => 1,
                'created_at' => now(),
            ]);

            for ($i = 1; $i <= 2; $i++) {
                ImeiNumber::create([
                    'product_variant_id' => $pixelVar->id,
                    'warehouse_id' => $warehouses['North']->id,
                    'imei' => '35194010000000' . $i,
                    'status' => 'available'
                ]);
            }

            // D. Tracked by IMEI: OnePlus 12
            $oneplus = Product::create([
                'brand_id' => $brands['OnePlus']->id,
                'category_id' => $categories['Phones']->id,
                'sub_category_id' => $subCategories['OnePlusSub']->id,
                'name' => 'OnePlus 12',
                'model_no' => 'CPH2581',
                'description' => 'Snapdragon 8 Gen 3, 4th Gen Hasselblad Camera, Emerald Flow.',
                'is_imei_tracked' => true,
                'status' => true
            ]);

            $oneplusVar = ProductVariant::create([
                'product_id' => $oneplus->id,
                'color_id' => $colors['Green']->id,
                'storage_variant_id' => $storage['256G']->id,
                'ram_variant_id' => $ram['16G']->id,
                'sku' => 'OP12-EG-256-16',
                'cost_price' => 599.00,
                'selling_price' => 699.00,
                'alert_quantity' => 2,
                'status' => true
            ]);

            // Seed stock in Warehouse South (Quantity: 4)
            Stock::create([
                'warehouse_id' => $warehouses['South']->id,
                'product_variant_id' => $oneplusVar->id,
                'quantity' => 4
            ]);

            StockLedger::create([
                'warehouse_id' => $warehouses['South']->id,
                'product_variant_id' => $oneplusVar->id,
                'type' => 'in',
                'quantity' => 4,
                'reference_type' => 'Opening Stock',
                'old_quantity' => 0,
                'new_quantity' => 4,
                'user_id' => 1,
                'created_at' => now(),
            ]);

            for ($i = 1; $i <= 4; $i++) {
                ImeiNumber::create([
                    'product_variant_id' => $oneplusVar->id,
                    'warehouse_id' => $warehouses['South']->id,
                    'imei' => '35661910000000' . $i,
                    'status' => 'available'
                ]);
            }

            // E. NOT Tracked by IMEI: Charger 20W
            $charger = Product::create([
                'brand_id' => $brands['Apple']->id,
                'category_id' => $categories['Accessories']->id,
                'sub_category_id' => $subCategories['Chargers']->id,
                'name' => 'Fast Charger 20W',
                'model_no' => 'MHJH3AM/A',
                'description' => 'USB-C Power Adapter for rapid charging.',
                'is_imei_tracked' => false,
                'status' => true
            ]);

            $chargerVar = ProductVariant::create([
                'product_id' => $charger->id,
                'color_id' => null,
                'storage_variant_id' => null,
                'ram_variant_id' => null,
                'sku' => 'CHG-20W-APPLE',
                'cost_price' => 15.00,
                'selling_price' => 29.00,
                'alert_quantity' => 10,
                'status' => true
            ]);

            Stock::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $chargerVar->id,
                'quantity' => 50
            ]);

            StockLedger::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $chargerVar->id,
                'type' => 'in',
                'quantity' => 50,
                'reference_type' => 'Opening Stock',
                'old_quantity' => 0,
                'new_quantity' => 50,
                'user_id' => 1,
                'created_at' => now(),
            ]);

            // F. NOT Tracked by IMEI: Galaxy Buds 2 Pro
            $buds = Product::create([
                'brand_id' => $brands['Samsung']->id,
                'category_id' => $categories['Accessories']->id,
                'sub_category_id' => $subCategories['Earbuds']->id,
                'name' => 'Galaxy Buds 2 Pro',
                'model_no' => 'SM-R510',
                'description' => '24-bit Hi-Fi audio, Intelligent ANC, comfortable fit.',
                'is_imei_tracked' => false,
                'status' => true
            ]);

            $budsVar1 = ProductVariant::create([
                'product_id' => $buds->id,
                'color_id' => $colors['Black']->id,
                'storage_variant_id' => null,
                'ram_variant_id' => null,
                'sku' => 'BUDS2P-MB',
                'cost_price' => 99.00,
                'selling_price' => 149.00,
                'alert_quantity' => 5,
                'status' => true
            ]);

            $budsVar2 = ProductVariant::create([
                'product_id' => $buds->id,
                'color_id' => $colors['White']->id,
                'storage_variant_id' => null,
                'ram_variant_id' => null,
                'sku' => 'BUDS2P-SW',
                'cost_price' => 99.00,
                'selling_price' => 149.00,
                'alert_quantity' => 5,
                'status' => true
            ]);

            Stock::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $budsVar1->id,
                'quantity' => 20
            ]);

            StockLedger::create([
                'warehouse_id' => $warehouses['North']->id,
                'product_variant_id' => $budsVar1->id,
                'type' => 'in',
                'quantity' => 20,
                'reference_type' => 'Opening Stock',
                'old_quantity' => 0,
                'new_quantity' => 20,
                'user_id' => 1,
                'created_at' => now(),
            ]);

            Stock::create([
                'warehouse_id' => $warehouses['South']->id,
                'product_variant_id' => $budsVar2->id,
                'quantity' => 15
            ]);

            StockLedger::create([
                'warehouse_id' => $warehouses['South']->id,
                'product_variant_id' => $budsVar2->id,
                'type' => 'in',
                'quantity' => 15,
                'reference_type' => 'Opening Stock',
                'old_quantity' => 0,
                'new_quantity' => 15,
                'user_id' => 1,
                'created_at' => now(),
            ]);
        });
    }
}
