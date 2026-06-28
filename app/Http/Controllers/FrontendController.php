<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\StorageVariant;
use App\Models\RamVariant;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    /**
     * Public Homepage.
     */
    public function home()
    {
        // 1. Optimized Query for Featured Products
        $featuredVariants = ProductVariant::with([
            'product.brand', 
            'product.category', 
            'color', 
            'storageVariant', 
            'ramVariant', 
            'stocks'
        ])
        ->where('status', true)
        ->latest('id')
        ->take(4)
        ->get();

        $brands = Brand::where('status', true)->take(6)->get();
        $categories = Category::where('status', true)->take(3)->get();

        return view('frontend.home', compact('featuredVariants', 'brands', 'categories'));
    }

    /**
     * Public Shop Catalog Page (Search, Specification filters, Price range filters).
     */
    public function shop(Request $request)
    {
        // 1. Core Optimized Query with Eager Loading (Prevents N+1 queries)
        $query = ProductVariant::with([
            'product.brand', 
            'product.category', 
            'color', 
            'storageVariant', 
            'ramVariant', 
            'stocks'
        ])
        ->where('status', true)
        ->whereHas('product', function($q) {
            $q->where('status', true);
        });

        // 2. Search filter
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('model_no', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Category filter
        if ($categoryIds = $request->input('categories')) {
            $query->whereHas('product', function($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
        }

        // 4. Brand filter
        if ($brandIds = $request->input('brands')) {
            $query->whereHas('product', function($q) use ($brandIds) {
                $q->whereIn('brand_id', $brandIds);
            });
        }

        // 5. Specification: Color filter
        if ($colorIds = $request->input('colors')) {
            $query->whereIn('color_id', $colorIds);
        }

        // 6. Specification: Storage filter
        if ($storageIds = $request->input('storage')) {
            $query->whereIn('storage_variant_id', $storageIds);
        }

        // 7. Specification: RAM filter
        if ($ramIds = $request->input('ram')) {
            $query->whereIn('ram_variant_id', $ramIds);
        }

        // 8. Price range filter
        if ($minPrice = $request->input('min_price')) {
            $query->where('selling_price', '>=', $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('selling_price', '<=', $maxPrice);
        }

        // 9. Sorting
        $sort = $request->input('sort', 'latest');
        if ($sort === 'price_low') {
            $query->orderBy('selling_price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('selling_price', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $variants = $query->paginate(9);

        // 10. AJAX partial grid response
        if ($request->ajax()) {
            return view('frontend.partials.product_grid', compact('variants'))->render();
        }

        // Load filter list data
        $filterCategories = Category::where('status', true)->get();
        $filterBrands = Brand::where('status', true)->get();
        $filterColors = Color::where('status', true)->get();
        $filterStorage = StorageVariant::where('status', true)->get();
        $filterRam = RamVariant::where('status', true)->get();

        return view('frontend.shop', compact(
            'variants', 
            'filterCategories', 
            'filterBrands', 
            'filterColors', 
            'filterStorage', 
            'filterRam'
        ));
    }
}
