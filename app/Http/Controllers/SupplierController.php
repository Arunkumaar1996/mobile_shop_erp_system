<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-contacts');

        if ($request->ajax()) {
            $query = Supplier::select('suppliers.*');

            // Apply Search
            if ($search = $request->input('search.value')) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%");
                });
            }

            // Apply Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            $totalData = Supplier::count();
            $totalFiltered = $query->count();

            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $suppliers = $query->offset($start)->limit($limit)->orderBy('id', 'desc')->get();

            $data = [];
            foreach ($suppliers as $s) {
                $statusChecked = $s->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-status" type="checkbox" data-id="' . $s->id . '" ' . $statusChecked . '>
                </div>';

                $actions = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->hasPermission('manage-contacts')) {
                    $actions .= '<button class="btn btn-outline-primary edit-btn" data-id="' . $s->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                    $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $s->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';

                $nestedData['id'] = $s->id;
                $nestedData['name'] = e($s->name) . '<br><small class="text-muted">Contact: ' . e($s->contact_person ?? 'N/A') . '</small>';
                $nestedData['phone'] = e($s->phone ?? 'N/A');
                $nestedData['email'] = e($s->email ?? 'N/A');
                $nestedData['gstin'] = e($s->gstin ?? 'N/A');
                $nestedData['outstanding_balance'] = '$' . number_format($s->outstanding_balance, 2);
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

        return view('suppliers.index');
    }

    public function store(SupplierStoreRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        ActivityLog::log('Created Supplier', 'Supplier', $supplier->id, [
            'name' => $supplier->name,
            'phone' => $supplier->phone
        ]);

        return response()->json(['success' => 'Supplier added successfully.']);
    }

    public function show(Supplier $supplier)
    {
        $this->authorize('view-contacts');
        return response()->json($supplier);
    }

    public function update(SupplierUpdateRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        ActivityLog::log('Updated Supplier', 'Supplier', $supplier->id, [
            'name' => $supplier->name,
            'phone' => $supplier->phone
        ]);

        return response()->json(['success' => 'Supplier details updated successfully.']);
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('manage-contacts');

        $supplier->delete();

        ActivityLog::log('Deleted Supplier', 'Supplier', $supplier->id, [
            'name' => $supplier->name,
            'phone' => $supplier->phone
        ]);

        return response()->json(['success' => 'Supplier soft-deleted successfully.']);
    }

    public function toggleStatus(Request $request, Supplier $supplier)
    {
        $this->authorize('manage-contacts');

        $supplier->status = !$supplier->status;
        $supplier->save();

        ActivityLog::log('Toggled Supplier Status', 'Supplier', $supplier->id, [
            'name' => $supplier->name,
            'status' => $supplier->status
        ]);

        return response()->json(['success' => 'Supplier status updated successfully.']);
    }

    public function export()
    {
        $this->authorize('view-contacts');

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=suppliers_list.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $suppliers = Supplier::all();

        $callback = function() use($suppliers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Contact Person', 'Phone', 'Email', 'Address', 'GSTIN', 'Outstanding Balance', 'Status']);

            foreach ($suppliers as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->name,
                    $s->contact_person ?? 'N/A',
                    $s->phone ?? 'N/A',
                    $s->email ?? 'N/A',
                    $s->address ?? 'N/A',
                    $s->gstin ?? 'N/A',
                    $s->outstanding_balance,
                    $s->status ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printList()
    {
        $this->authorize('view-contacts');
        $suppliers = Supplier::all();
        return view('suppliers.print', compact('suppliers'));
    }
}
