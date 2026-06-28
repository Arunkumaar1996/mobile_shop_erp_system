<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\RamVariant;
use App\Models\StorageVariant;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-products');

        if ($request->ajax()) {
            // Check request type
            $type = $request->input('type');
            if ($type === 'colors') {
                $colors = Color::orderBy('id', 'desc')->get();
                return response()->json(['data' => $colors]);
            } elseif ($type === 'storage') {
                $storage = StorageVariant::orderBy('id', 'desc')->get();
                return response()->json(['data' => $storage]);
            } elseif ($type === 'ram') {
                $ram = RamVariant::orderBy('id', 'desc')->get();
                return response()->json(['data' => $ram]);
            }
        }

        return view('variants.index');
    }

    /* 1. Colors AJAX CRUD */
    public function storeColor(Request $request)
    {
        $this->authorize('create-products');
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:colors,name',
            'code' => 'nullable|string|max:10'
        ]);

        $color = Color::create($data);
        ActivityLog::log('Created Color', 'Color', $color->id, ['name' => $color->name]);

        return response()->json(['success' => 'Color created successfully.']);
    }

    public function showColor(Color $color)
    {
        $this->authorize('view-products');
        return response()->json($color);
    }

    public function updateColor(Request $request, Color $color)
    {
        $this->authorize('edit-products');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('colors')->ignore($color->id)],
            'code' => 'nullable|string|max:10'
        ]);

        $color->update($data);
        ActivityLog::log('Updated Color', 'Color', $color->id, ['name' => $color->name]);

        return response()->json(['success' => 'Color updated successfully.']);
    }

    public function destroyColor(Color $color)
    {
        $this->authorize('delete-products');
        if ($color->productVariants()->exists()) {
            return response()->json(['error' => 'Cannot delete color as it is linked to product variants.'], 400);
        }

        $color->delete();
        ActivityLog::log('Deleted Color', 'Color', $color->id, ['name' => $color->name]);

        return response()->json(['success' => 'Color deleted successfully.']);
    }

    /* 2. Storage AJAX CRUD */
    public function storeStorage(Request $request)
    {
        $this->authorize('create-products');
        $data = $request->validate([
            'value' => 'required|string|max:50|unique:storage_variants,value'
        ]);

        $storage = StorageVariant::create($data);
        ActivityLog::log('Created Storage Variant', 'StorageVariant', $storage->id, ['value' => $storage->value]);

        return response()->json(['success' => 'Storage variant created successfully.']);
    }

    public function showStorage(StorageVariant $storage)
    {
        $this->authorize('view-products');
        return response()->json($storage);
    }

    public function updateStorage(Request $request, StorageVariant $storage)
    {
        $this->authorize('edit-products');
        $data = $request->validate([
            'value' => ['required', 'string', 'max:50', Rule::unique('storage_variants')->ignore($storage->id)]
        ]);

        $storage->update($data);
        ActivityLog::log('Updated Storage Variant', 'StorageVariant', $storage->id, ['value' => $storage->value]);

        return response()->json(['success' => 'Storage variant updated successfully.']);
    }

    public function destroyStorage(StorageVariant $storage)
    {
        $this->authorize('delete-products');
        if ($storage->productVariants()->exists()) {
            return response()->json(['error' => 'Cannot delete storage variant as it is linked to product variants.'], 400);
        }

        $storage->delete();
        ActivityLog::log('Deleted Storage Variant', 'StorageVariant', $storage->id, ['value' => $storage->value]);

        return response()->json(['success' => 'Storage variant deleted successfully.']);
    }

    /* 3. RAM AJAX CRUD */
    public function storeRam(Request $request)
    {
        $this->authorize('create-products');
        $data = $request->validate([
            'value' => 'required|string|max:50|unique:ram_variants,value'
        ]);

        $ram = RamVariant::create($data);
        ActivityLog::log('Created RAM Variant', 'RamVariant', $ram->id, ['value' => $ram->value]);

        return response()->json(['success' => 'RAM variant created successfully.']);
    }

    public function showRam(RamVariant $ram)
    {
        $this->authorize('view-products');
        return response()->json($ram);
    }

    public function updateRam(Request $request, RamVariant $ram)
    {
        $this->authorize('edit-products');
        $data = $request->validate([
            'value' => ['required', 'string', 'max:50', Rule::unique('ram_variants')->ignore($ram->id)]
        ]);

        $ram->update($data);
        ActivityLog::log('Updated RAM Variant', 'RamVariant', $ram->id, ['value' => $ram->value]);

        return response()->json(['success' => 'RAM variant updated successfully.']);
    }

    public function destroyRam(RamVariant $ram)
    {
        $this->authorize('delete-products');
        if ($ram->productVariants()->exists()) {
            return response()->json(['error' => 'Cannot delete RAM variant as it is linked to product variants.'], 400);
        }

        $ram->delete();
        ActivityLog::log('Deleted RAM Variant', 'RamVariant', $ram->id, ['value' => $ram->value]);

        return response()->json(['success' => 'RAM variant deleted successfully.']);
    }
}
