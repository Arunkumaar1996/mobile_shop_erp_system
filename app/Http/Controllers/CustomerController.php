<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-contacts');

        if ($request->ajax()) {
            $query = Customer::select('customers.*');

            // Apply Search
            if ($search = $request->input('search.value')) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Apply Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            $totalData = Customer::count();
            $totalFiltered = $query->count();

            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $customers = $query->offset($start)->limit($limit)->orderBy('id', 'desc')->get();

            $data = [];
            foreach ($customers as $c) {
                $statusChecked = $c->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-status" type="checkbox" data-id="' . $c->id . '" ' . $statusChecked . '>
                </div>';

                $actions = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->hasPermission('manage-contacts')) {
                    $actions .= '<button class="btn btn-outline-primary edit-btn" data-id="' . $c->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                    $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $c->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';

                $nestedData['id'] = $c->id;
                $nestedData['name'] = e($c->name);
                $nestedData['phone'] = e($c->phone);
                $nestedData['email'] = e($c->email ?? 'N/A');
                $nestedData['wallet_balance'] = '$' . number_format($c->wallet_balance, 2);
                $nestedData['loyalty_points'] = $c->loyalty_points;
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

        return view('customers.index');
    }

    public function store(CustomerStoreRequest $request)
    {
        $customer = Customer::create($request->validated());

        ActivityLog::log('Created Customer', 'Customer', $customer->id, [
            'name' => $customer->name,
            'phone' => $customer->phone
        ]);

        return response()->json(['success' => 'Customer added successfully.']);
    }

    public function show(Customer $customer)
    {
        $this->authorize('view-contacts');
        return response()->json($customer);
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        ActivityLog::log('Updated Customer', 'Customer', $customer->id, [
            'name' => $customer->name,
            'phone' => $customer->phone
        ]);

        return response()->json(['success' => 'Customer details updated successfully.']);
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('manage-contacts');

        $customer->delete();

        ActivityLog::log('Deleted Customer', 'Customer', $customer->id, [
            'name' => $customer->name,
            'phone' => $customer->phone
        ]);

        return response()->json(['success' => 'Customer soft-deleted successfully.']);
    }

    public function toggleStatus(Request $request, Customer $customer)
    {
        $this->authorize('manage-contacts');

        $customer->status = !$customer->status;
        $customer->save();

        ActivityLog::log('Toggled Customer Status', 'Customer', $customer->id, [
            'name' => $customer->name,
            'status' => $customer->status
        ]);

        return response()->json(['success' => 'Customer status updated successfully.']);
    }

    public function export()
    {
        $this->authorize('view-contacts');

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=customers_list.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $customers = Customer::all();

        $callback = function() use($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Phone', 'Email', 'Address', 'Wallet Balance', 'Loyalty Points', 'Status']);

            foreach ($customers as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->name,
                    $c->phone,
                    $c->email ?? 'N/A',
                    $c->address ?? 'N/A',
                    $c->wallet_balance,
                    $c->loyalty_points,
                    $c->status ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printList()
    {
        $this->authorize('view-contacts');
        $customers = Customer::all();
        return view('customers.print', compact('customers'));
    }
}
