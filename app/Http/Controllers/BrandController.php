<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandStoreRequest;
use App\Http\Requests\BrandUpdateRequest;
use App\Models\Brand;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-products');

        if ($request->ajax()) {
            $brands = Brand::orderBy('id', 'desc')->get();
            
            $data = [];
            foreach ($brands as $b) {
                $statusChecked = $b->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-status" type="checkbox" data-id="' . $b->id . '" ' . $statusChecked . '>
                </div>';

                $actions = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->hasPermission('edit-products')) {
                    $actions .= '<button class="btn btn-outline-primary edit-btn" data-id="' . $b->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermission('delete-products')) {
                    $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $b->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';

                $nestedData['id'] = $b->id;
                $nestedData['name'] = e($b->name);
                $nestedData['status'] = $statusSwitch;
                $nestedData['actions'] = $actions;
                $data[] = $nestedData;
            }

            return response()->json(['data' => $data]);
        }

        return view('brands.index');
    }

    public function store(BrandStoreRequest $request)
    {
        $brand = Brand::create($request->validated());

        ActivityLog::log('Created Brand', 'Brand', $brand->id, ['name' => $brand->name]);

        return response()->json(['success' => 'Brand created successfully.']);
    }

    public function show(Brand $brand)
    {
        $this->authorize('view-products');
        return response()->json($brand);
    }

    public function update(BrandUpdateRequest $request, Brand $brand)
    {
        $brand->update($request->validated());

        ActivityLog::log('Updated Brand', 'Brand', $brand->id, ['name' => $brand->name]);

        return response()->json(['success' => 'Brand updated successfully.']);
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('delete-products');

        if ($brand->products()->exists()) {
            return response()->json(['error' => 'Cannot delete brand as it has associated products.'], 400);
        }

        $brand->delete();

        ActivityLog::log('Deleted Brand', 'Brand', $brand->id, ['name' => $brand->name]);

        return response()->json(['success' => 'Brand deleted successfully.']);
    }

    public function toggleStatus(Request $request, Brand $brand)
    {
        $this->authorize('edit-products');

        $brand->status = !$brand->status;
        $brand->save();

        ActivityLog::log('Toggled Brand Status', 'Brand', $brand->id, [
            'name' => $brand->name,
            'status' => $brand->status
        ]);

        return response()->json(['success' => 'Brand status updated successfully.']);
    }
}
