<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-roles');

        if ($request->ajax()) {
            $roles = Role::withCount('users')->get();
            
            $data = [];
            foreach ($roles as $r) {
                $isSystemRole = in_array($r->name, ['super-admin', 'admin']);
                
                $actions = '<div class="btn-group btn-group-sm">';
                if (auth()->user()->hasPermission('manage-roles')) {
                    $actions .= '<button class="btn btn-outline-primary edit-btn" data-id="' . $r->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                    
                    if (!$isSystemRole && $r->users_count === 0) {
                        $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $r->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                    }
                }
                $actions .= '</div>';

                $nestedData['id'] = $r->id;
                $nestedData['name'] = e($r->display_name) . '<br><small class="text-muted">' . e($r->name) . '</small>';
                $nestedData['description'] = e($r->description ?? 'N/A');
                $nestedData['users_count'] = $r->users_count;
                $nestedData['actions'] = $actions;
                $data[] = $nestedData;
            }

            return response()->json(['data' => $data]);
        }

        // Group permissions by module for layout
        $permissions = Permission::all()->groupBy('module');

        return view('roles.index', compact('permissions'));
    }

    public function store(RoleStoreRequest $request)
    {
        DB::transaction(function () use ($request) {
            $role = Role::create([
                'name' => $request->input('name'),
                'display_name' => $request->input('display_name'),
                'description' => $request->input('description'),
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->sync($request->input('permissions'));
            }

            ActivityLog::log('Created Role', 'Role', $role->id, [
                'name' => $role->name,
                'display_name' => $role->display_name,
            ]);
        });

        return response()->json(['success' => 'Role created successfully.']);
    }

    public function show(Role $role)
    {
        $this->authorize('view-roles');
        $role->load('permissions');
        $permissionIds = $role->permissions->pluck('id')->toArray();

        return response()->json([
            'role' => $role,
            'permissions' => $permissionIds
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        if (in_array($role->name, ['super-admin', 'admin']) && $request->input('name') !== $role->name) {
            return response()->json(['error' => 'You cannot change system role keys!'], 400);
        }

        DB::transaction(function () use ($request, $role) {
            $role->update([
                'name' => $request->input('name'),
                'display_name' => $request->input('display_name'),
                'description' => $request->input('description'),
            ]);

            // Sync permissions (exclude super-admin which is handled dynamically or always sync all)
            if ($role->name === 'super-admin') {
                $role->permissions()->sync(Permission::all());
            } elseif ($request->has('permissions')) {
                $role->permissions()->sync($request->input('permissions'));
            } else {
                $role->permissions()->detach();
            }

            ActivityLog::log('Updated Role', 'Role', $role->id, [
                'name' => $role->name,
                'display_name' => $role->display_name,
            ]);
        });

        return response()->json(['success' => 'Role updated successfully.']);
    }

    public function destroy(Role $role)
    {
        $this->authorize('manage-roles');

        if (in_array($role->name, ['super-admin', 'admin'])) {
            return response()->json(['error' => 'You cannot delete system roles!'], 400);
        }

        if ($role->users()->exists()) {
            return response()->json(['error' => 'You cannot delete a role with active users assigned!'], 400);
        }

        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();

            ActivityLog::log('Deleted Role', 'Role', $role->id, [
                'name' => $role->name,
                'display_name' => $role->display_name,
            ]);
        });

        return response()->json(['success' => 'Role deleted successfully.']);
    }
}
