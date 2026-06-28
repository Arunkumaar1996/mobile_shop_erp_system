<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Color;
use App\Models\StorageVariant;
use App\Models\RamVariant;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-products');

        if ($request->ajax()) {
            $query = Product::with(['brand', 'category', 'variants'])->select('products.*');

            // Apply Filters
            if ($brandId = $request->input('brand_id')) {
                $query->where('brand_id', $brandId);
            }
            if ($categoryId = $request->input('category_id')) {
                $query->where('category_id', $categoryId);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            // Apply Search
            if ($search = $request->input('search.value')) {
                $query->where(function($q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                      ->orWhere('products.model_no', 'like', "%{$search}%")
                      ->orWhereHas('brand', function($bQ) use ($search) {
                          $bQ->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('category', function($cQ) use ($search) {
                          $cQ->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $totalData = Product::count();
            $totalFiltered = $query->count();

            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $products = $query->offset($start)->limit($limit)->orderBy('id', 'desc')->get();

            $data = [];
            foreach ($products as $p) {
                $statusChecked = $p->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-status" type="checkbox" data-id="' . $p->id . '" ' . $statusChecked . '>
                </div>';

                $actions = '<div class="btn-group btn-group-sm">
                    <a href="' . route('products.edit', $p->id) . '" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>';
                if (auth()->user()->hasPermission('delete-products')) {
                    $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $p->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';
                $imgHtml = '';
                if ($p->image) {
                    $imgHtml = '<img src="' . asset('storage/' . $p->image) . '" alt="Image" class="rounded border me-2 float-start" width="36" height="36" style="object-fit: cover;">';
                } else {
                    $imgHtml = '<div class="rounded border bg-light d-flex align-items-center justify-content-center me-2 float-start text-muted" style="width: 36px; height: 36px;"><i class="bi bi-image" style="font-size: 0.9rem;"></i></div>';
                }

                $nestedData['id'] = $p->id;
                $nestedData['name'] = '<div class="d-flex align-items-center">' . $imgHtml . '<div>' . e($p->name) . '<br><small class="text-muted">Model: ' . e($p->model_no ?? 'N/A') . '</small></div></div>';
                $nestedData['brand'] = e($p->brand->name ?? 'N/A');
                $nestedData['category'] = e($p->category->name ?? 'N/A');
                $nestedData['variants_count'] = $p->variants->count();
                $nestedData['imei_tracked'] = $p->is_imei_tracked ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                $nestedData['status'] = $statusSwitch;
                $nestedData['actions'] = $actions;
                $data[] = $nestedData;
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ]);
        }

        $brands = Brand::where('status', true)->get();
        $categories = Category::where('status', true)->get();

        return view('products.index', compact('brands', 'categories'));
    }

    public function create()
    {
        $this->authorize('create-products');

        $brands = Brand::where('status', true)->get();
        $categories = Category::where('status', true)->get();
        $colors = Color::where('status', true)->get();
        $storage = StorageVariant::where('status', true)->get();
        $ram = RamVariant::where('status', true)->get();

        return view('products.create', compact('brands', 'categories', 'colors', 'storage', 'ram'));
    }

    public function store(ProductStoreRequest $request)
    {
        DB::transaction(function () use ($request) {
            $productData = $request->only('brand_id', 'category_id', 'sub_category_id', 'name', 'model_no', 'description');
            $productData['is_imei_tracked'] = $request->boolean('is_imei_tracked');
            $productData['status'] = $request->boolean('status', true);

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');
                $productData['image'] = $path;
            }

            $product = Product::create($productData);

            // Handle Gallery Images Upload
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $index => $file) {
                    $filename = time() . '_' . uniqid() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('products/gallery', $filename, 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Create Variants
            $variants = $request->input('variants', []);
            foreach ($variants as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id' => $v['color_id'] ?? null,
                    'storage_variant_id' => $v['storage_variant_id'] ?? null,
                    'ram_variant_id' => $v['ram_variant_id'] ?? null,
                    'sku' => $v['sku'],
                    'cost_price' => $v['cost_price'],
                    'selling_price' => $v['selling_price'],
                    'alert_quantity' => $v['alert_quantity'],
                    'status' => true
                ]);
            }

            ActivityLog::log('Created Product', 'Product', $product->id, [
                'name' => $product->name,
                'variants_count' => count($variants)
            ]);
        });

        return response()->json(['success' => 'Product catalog created successfully.']);
    }

    public function show(Product $product)
    {
        $this->authorize('view-products');
        $product->load(['brand', 'category', 'subCategory', 'variants.color', 'variants.storageVariant', 'variants.ramVariant']);
        return response()->json($product);
    }

    public function edit(Product $product)
    {
        $this->authorize('edit-products');
        $product->load(['variants', 'images']);

        $brands = Brand::where('status', true)->get();
        $categories = Category::where('status', true)->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->where('status', true)->get();
        $colors = Color::where('status', true)->get();
        $storage = StorageVariant::where('status', true)->get();
        $ram = RamVariant::where('status', true)->get();

        return view('products.edit', compact('product', 'brands', 'categories', 'subCategories', 'colors', 'storage', 'ram'));
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $productData = $request->only('brand_id', 'category_id', 'sub_category_id', 'name', 'model_no', 'description');
            $productData['is_imei_tracked'] = $request->boolean('is_imei_tracked');
            $productData['status'] = $request->boolean('status', true);

            // Handle Image Upload / Replace
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');
                $productData['image'] = $path;
            }

            $product->update($productData);

            // Delete requested gallery images
            if ($request->has('delete_gallery_ids')) {
                $deleteIds = $request->input('delete_gallery_ids');
                $imagesToDelete = $product->images()->whereIn('id', $deleteIds)->get();
                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }

            // Handle new Gallery Images Upload
            if ($request->hasFile('gallery_images')) {
                $nextSortOrder = intval($product->images()->max('sort_order')) + 1;
                foreach ($request->file('gallery_images') as $index => $file) {
                    $filename = time() . '_' . uniqid() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('products/gallery', $filename, 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $nextSortOrder + $index,
                    ]);
                }
            }

            $variants = $request->input('variants', []);
            $keepVariantIds = [];

            foreach ($variants as $v) {
                if (!empty($v['id'])) {
                    // Update existing
                    $pv = ProductVariant::findOrFail($v['id']);
                    $pv->update([
                        'color_id' => $v['color_id'] ?? null,
                        'storage_variant_id' => $v['storage_variant_id'] ?? null,
                        'ram_variant_id' => $v['ram_variant_id'] ?? null,
                        'sku' => $v['sku'],
                        'cost_price' => $v['cost_price'],
                        'selling_price' => $v['selling_price'],
                        'alert_quantity' => $v['alert_quantity'],
                    ]);
                    $keepVariantIds[] = $pv->id;
                } else {
                    // Create new during edit
                    $pv = ProductVariant::create([
                        'product_id' => $product->id,
                        'color_id' => $v['color_id'] ?? null,
                        'storage_variant_id' => $v['storage_variant_id'] ?? null,
                        'ram_variant_id' => $v['ram_variant_id'] ?? null,
                        'sku' => $v['sku'],
                        'cost_price' => $v['cost_price'],
                        'selling_price' => $v['selling_price'],
                        'alert_quantity' => $v['alert_quantity'],
                        'status' => true
                    ]);
                    $keepVariantIds[] = $pv->id;
                }
            }

            // Soft-Delete variants that are removed from payload (only if they have 0 stock to prevent orphaned transactions)
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $keepVariantIds)
                ->delete();

            ActivityLog::log('Updated Product', 'Product', $product->id, [
                'name' => $product->name,
                'variants_count' => count($variants)
            ]);
        });

        return response()->json(['success' => 'Product catalog updated successfully.']);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete-products');

        DB::transaction(function () use ($product) {
            $product->variants()->delete();
            $product->delete();

            ActivityLog::log('Deleted Product', 'Product', $product->id, ['name' => $product->name]);
        });

        return response()->json(['success' => 'Product deleted successfully.']);
    }

    public function toggleStatus(Request $request, Product $product)
    {
        $this->authorize('edit-products');

        $product->status = !$product->status;
        $product->save();

        ActivityLog::log('Toggled Product Status', 'Product', $product->id, [
            'name' => $product->name,
            'status' => $product->status
        ]);

        return response()->json(['success' => 'Product status updated successfully.']);
    }

    /* Helper endpoints for Purchases/Sales POS modules */

    public function getVariants(Product $product)
    {
        $this->authorize('view-products');
        
        $variants = ProductVariant::with(['color', 'storageVariant', 'ramVariant'])
            ->where('product_id', $product->id)
            ->where('status', true)
            ->get();

        return response()->json($variants);
    }
}
