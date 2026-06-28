<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-products');

        if ($request->ajax()) {
            $categories = Category::withCount('subCategories')->orderBy('id', 'desc')->get();
            
            $data = [];
            foreach ($categories as $c) {
                $statusChecked = $c->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-status" type="checkbox" data-id="' . $c->id . '" ' . $statusChecked . '>
                </div>';

                $actions = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->hasPermission('edit-products')) {
                    $actions .= '<button class="btn btn-outline-primary edit-btn" data-id="' . $c->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermission('delete-products')) {
                    $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $c->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';

                $nestedData['id'] = $c->id;
                $nestedData['name'] = e($c->name);
                $nestedData['subcategories_count'] = $c->sub_categories_count;
                $nestedData['status'] = $statusSwitch;
                $nestedData['actions'] = $actions;
                $data[] = $nestedData;
            }

            return response()->json(['data' => $data]);
        }

        return view('categories.index');
    }

    public function store(CategoryStoreRequest $request)
    {
        $category = Category::create($request->validated());

        ActivityLog::log('Created Category', 'Category', $category->id, ['name' => $category->name]);

        return response()->json(['success' => 'Category created successfully.']);
    }

    public function show(Category $category)
    {
        $this->authorize('view-products');
        return response()->json($category);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $category->update($request->validated());

        ActivityLog::log('Updated Category', 'Category', $category->id, ['name' => $category->name]);

        return response()->json(['success' => 'Category updated successfully.']);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete-products');

        if ($category->products()->exists() || $category->subCategories()->whereHas('products')->exists()) {
            return response()->json(['error' => 'Cannot delete category as it has associated products.'], 400);
        }

        $category->delete();

        ActivityLog::log('Deleted Category', 'Category', $category->id, ['name' => $category->name]);

        return response()->json(['success' => 'Category deleted successfully.']);
    }

    public function toggleStatus(Request $request, Category $category)
    {
        $this->authorize('edit-products');

        $category->status = !$category->status;
        $category->save();

        ActivityLog::log('Toggled Category Status', 'Category', $category->id, [
            'name' => $category->name,
            'status' => $category->status
        ]);

        return response()->json(['success' => 'Category status updated successfully.']);
    }

    /* SubCategory AJAX Helpers */

    public function getSubCategories(Category $category)
    {
        $this->authorize('view-products');
        $subCategories = $category->subCategories()->where('status', true)->get(['id', 'name']);
        return response()->json($subCategories);
    }

    public function listSubCategories(Request $request)
    {
        $this->authorize('view-products');

        $subcategories = SubCategory::with('category')->orderBy('id', 'desc')->get();
        
        if ($request->ajax()) {
            $data = [];
            foreach ($subcategories as $sc) {
                $statusChecked = $sc->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-subcategory-status" type="checkbox" data-id="' . $sc->id . '" ' . $statusChecked . '>
                </div>';

                $actions = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->hasPermission('edit-products')) {
                    $actions .= '<button class="btn btn-outline-primary edit-subcategory-btn" data-id="' . $sc->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermission('delete-products')) {
                    $actions .= '<button class="btn btn-outline-danger delete-subcategory-btn" data-id="' . $sc->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';

                $nestedData['id'] = $sc->id;
                $nestedData['category'] = e($sc->category->name ?? 'N/A');
                $nestedData['name'] = e($sc->name);
                $nestedData['status'] = $statusSwitch;
                $nestedData['actions'] = $actions;
                $data[] = $nestedData;
            }
            return response()->json(['data' => $data]);
        }
    }

    public function storeSubCategory(Request $request)
    {
        $this->authorize('create-products');

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('sub_categories')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->input('category_id'));
                })
            ]
        ], [
            'name.unique' => 'Subcategory name already exists in this category.'
        ]);

        $sub = SubCategory::create($request->only('category_id', 'name'));

        ActivityLog::log('Created Subcategory', 'SubCategory', $sub->id, ['name' => $sub->name]);

        return response()->json(['success' => 'Subcategory created successfully.']);
    }

    public function showSubCategory(SubCategory $subCategory)
    {
        $this->authorize('view-products');
        return response()->json($subCategory);
    }

    public function updateSubCategory(Request $request, SubCategory $subCategory)
    {
        $this->authorize('edit-products');

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('sub_categories')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->input('category_id'));
                })->ignore($subCategory->id)
            ]
        ], [
            'name.unique' => 'Subcategory name already exists in this category.'
        ]);

        $subCategory->update($request->only('category_id', 'name'));

        ActivityLog::log('Updated Subcategory', 'SubCategory', $subCategory->id, ['name' => $subCategory->name]);

        return response()->json(['success' => 'Subcategory updated successfully.']);
    }

    public function destroySubCategory(SubCategory $subCategory)
    {
        $this->authorize('delete-products');

        if ($subCategory->products()->exists()) {
            return response()->json(['error' => 'Cannot delete subcategory as it has associated products.'], 400);
        }

        $subCategory->delete();

        ActivityLog::log('Deleted Subcategory', 'SubCategory', $subCategory->id, ['name' => $subCategory->name]);

        return response()->json(['success' => 'Subcategory deleted successfully.']);
    }

    public function toggleSubCategoryStatus(Request $request, SubCategory $subCategory)
    {
        $this->authorize('edit-products');

        $subCategory->status = !$subCategory->status;
        $subCategory->save();

        ActivityLog::log('Toggled Subcategory Status', 'SubCategory', $subCategory->id, [
            'name' => $subCategory->name,
            'status' => $subCategory->status
        ]);

        return response()->json(['success' => 'Subcategory status updated successfully.']);
    }
}
