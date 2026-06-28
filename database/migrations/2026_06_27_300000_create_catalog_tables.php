<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->unique(['category_id', 'name']);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable(); // Hex color e.g. #000000
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('storage_variants', function (Blueprint $table) {
            $table->id();
            $table->string('value')->unique(); // e.g. 128GB, 256GB
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('ram_variants', function (Blueprint $table) {
            $table->id();
            $table->string('value')->unique(); // e.g. 6GB, 8GB, 12GB
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->onDelete('set null');
            $table->string('name');
            $table->string('model_no')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_imei_tracked')->default(false); // Track individually by IMEI (mobile phones) or standard stock (accessories)
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('color_id')->nullable()->constrained('colors')->onDelete('set null');
            $table->foreignId('storage_variant_id')->nullable()->constrained('storage_variants')->onDelete('set null');
            $table->foreignId('ram_variant_id')->nullable()->constrained('ram_variants')->onDelete('set null');
            $table->string('sku')->unique();
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->integer('alert_quantity')->default(5);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('imei_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->string('imei')->unique();
            $table->enum('status', ['available', 'sold', 'transferred', 'returned', 'adjustment'])->default('available');
            $table->unsignedBigInteger('purchase_invoice_item_id')->nullable(); // Track purchase item origin (will bind to Purchases module later)
            $table->unsignedBigInteger('sales_item_id')->nullable(); // Track sales destination (will bind to Sales module later)
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imei_numbers');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('ram_variants');
        Schema::dropIfExists('storage_variants');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
