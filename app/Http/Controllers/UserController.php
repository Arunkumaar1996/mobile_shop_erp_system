<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-users');

        if ($request->ajax()) {
            $query = User::with(['branch', 'roles'])->select('users.*');

            // Apply Search
            if ($search = $request->input('search.value')) {
                $query->where(function($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%")
                      ->orWhere('users.phone', 'like', "%{$search}%")
                      ->orWhere('users.username', 'like', "%{$search}%");
                });
            }

            // Apply Branch Filter
            if ($branchId = $request->input('branch_id')) {
                $query->where('users.branch_id', $branchId);
            }

            // Apply Status Filter
            if ($request->filled('status')) {
                $query->where('users.status', $request->input('status'));
            }

            // Datatable formatting
            $totalData = User::count();
            $totalFiltered = $query->count();

            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            
            // Order
            if ($orderColumnIdx = $request->input('order.0.column')) {
                $orderColumn = $request->input("columns.{$orderColumnIdx}.data", 'id');
                $orderDir = $request->input('order.0.dir', 'desc');
                $query->orderBy($orderColumn, $orderDir);
            } else {
                $query->orderBy('users.id', 'desc');
            }

            $users = $query->offset($start)->limit($limit)->get();

            $data = [];
            foreach ($users as $u) {
                $roleBadge = '';
                foreach ($u->roles as $role) {
                    $roleBadge .= '<span class="badge bg-primary me-1">' . e($role->display_name) . '</span>';
                }

                $statusChecked = $u->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-status" type="checkbox" data-id="' . $u->id . '" ' . $statusChecked . '>
                </div>';

                $nestedData['checkbox'] = '<input type="checkbox" class="form-check-input select-row" value="' . $u->id . '">';
                $nestedData['id'] = $u->id;
                $nestedData['name'] = e($u->name) . '<br><small class="text-muted">@' . e($u->username) . '</small>';
                $nestedData['email'] = e($u->email);
                $nestedData['phone'] = e($u->phone ?? 'N/A');
                $nestedData['branch'] = e($u->branch->name ?? 'N/A');
                $nestedData['role'] = $roleBadge;
                $nestedData['status'] = $statusSwitch;
                
                $actions = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->hasPermission('edit-users')) {
                    $actions .= '<button class="btn btn-outline-primary edit-btn" data-id="' . $u->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermission('delete-users') && $u->id !== auth()->id()) {
                    $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $u->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';

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

        $branches = Branch::where('status', true)->get();
        $roles = Role::all();

        return view('users.index', compact('branches', 'roles'));
    }

    public function store(UserStoreRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            
            $user = User::create($data);
            $user->roles()->attach($request->input('role_id'));

            ActivityLog::log('Created User', 'User', $user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
            ]);
        });

        return response()->json(['success' => 'User created successfully.']);
    }

    public function show(User $user)
    {
        $this->authorize('view-users');
        $user->load(['branch', 'roles']);
        $roleId = $user->roles->first()?->id;

        return response()->json([
            'user' => $user,
            'role_id' => $roleId
        ]);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $data = $request->validated();
            
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);
            $user->roles()->sync([$request->input('role_id')]);

            ActivityLog::log('Updated User', 'User', $user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
            ]);
        });

        return response()->json(['success' => 'User updated successfully.']);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete-users');

        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot delete yourself!'], 400);
        }

        $user->delete();

        ActivityLog::log('Deleted User', 'User', $user->id, [
            'name' => $user->name,
            'email' => $user->email
        ]);

        return response()->json(['success' => 'User deleted successfully.']);
    }

    public function toggleStatus(Request $request, User $user)
    {
        $this->authorize('edit-users');
        
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot suspend yourself!'], 400);
        }

        $user->status = !$user->status;
        $user->save();

        ActivityLog::log('Toggled User Status', 'User', $user->id, [
            'name' => $user->name,
            'status' => $user->status
        ]);

        return response()->json(['success' => 'User status updated successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $this->authorize('delete-users');

        $ids = $request->input('ids', []);
        if (in_array(auth()->id(), $ids)) {
            return response()->json(['error' => 'You cannot delete yourself in bulk operations!'], 400);
        }

        if (empty($ids)) {
            return response()->json(['error' => 'No rows selected.'], 400);
        }

        User::whereIn('id', $ids)->delete();

        ActivityLog::log('Bulk Deleted Users', 'User', null, ['ids' => $ids]);

        return response()->json(['success' => 'Selected users deleted successfully.']);
    }

    public function export()
    {
        $this->authorize('view-users');

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=users_list.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $users = User::with(['branch', 'roles'])->get();

        $callback = function() use($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Username', 'Email', 'Phone', 'Branch', 'Role', 'Status']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->username,
                    $user->email,
                    $user->phone ?? 'N/A',
                    $user->branch->name ?? 'N/A',
                    $user->roles->first()?->display_name ?? 'N/A',
                    $user->status ? 'Active' : 'Suspended'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printList()
    {
        $this->authorize('view-users');
        $users = User::with(['branch', 'roles'])->get();
        return view('users.print', compact('users'));
    }
}
