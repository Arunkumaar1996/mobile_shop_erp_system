@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('module-title', 'Administration')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Roles & Permissions</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .permission-group-card {
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        background-color: var(--bg-app);
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .permission-group-header {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
        margin-bottom: 0.75rem;
        color: var(--primary-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>Roles Registry</h4>
    @can('manage-roles')
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#roleModal" id="add-role-btn">
        <i class="bi bi-shield-plus me-1"></i> Add New Role
    </button>
    @endcan
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        Role Accounts & User Counts
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="roles-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th width="150">Active Users</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Role AJAX Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="roleModalLabel">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="role-form" autocomplete="off">
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="role-id">
                
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="display_name" class="form-label">Role Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="display_name" class="form-control" required placeholder="e.g. Sales Executive">
                            <div class="invalid-feedback" id="err-display_name"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">System Key / Slug <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. sales-executive">
                            <small class="text-muted">Use lowercase alphanumeric and hyphens only.</small>
                            <div class="invalid-feedback" id="err-name"></div>
                        </div>
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control" placeholder="Brief details about what this role does">
                            <div class="invalid-feedback" id="err-description"></div>
                        </div>
                    </div>

                    <!-- Permissions Checkbox Matrix -->
                    <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-shield-check me-2"></i>Configure Permissions Matrix</h6>
                    
                    <div class="row">
                        @foreach($permissions as $module => $modulePerms)
                        <div class="col-lg-6">
                            <div class="permission-group-card">
                                <div class="permission-group-header">
                                    <span>{{ $module }}</span>
                                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none select-module-all" data-module="{{ Str::slug($module) }}">Select All</button>
                                </div>
                                <div class="row g-2">
                                    @foreach($modulePerms as $perm)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm-{{ $perm->id }}" class="form-check-input perm-checkbox perm-module-{{ Str::slug($module) }}">
                                            <label class="form-check-label fs-7" for="perm-{{ $perm->id }}">
                                                {{ $perm->display_name }}
                                                @if($perm->type === 'menu')
                                                    <i class="bi bi-menu-button-wide text-info ms-1" title="Menu wise Access"></i>
                                                @else
                                                    <i class="bi bi-app text-secondary ms-1" title="Button wise Action"></i>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-role-btn">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    // 1. Initialize DataTable
    var table = $('#roles-table').DataTable({
        processing: true,
        serverSide: false, // Standard datatable is fine since roles are typically few
        ajax: {
            url: "{{ route('roles.index') }}"
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'description'},
            {data: 'users_count'},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'asc']]
    });

    // Auto-generate name/slug slugification
    $('#display_name').on('keyup', function() {
        if ($('#form-method').val() === 'POST') {
            var slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            $('#name').val(slug);
        }
    });

    // 2. Select All in Module Helper
    $(document).on('click', '.select-module-all', function() {
        var moduleSlug = $(this).data('module');
        var checkboxes = $('.perm-module-' + moduleSlug);
        var btn = $(this);
        
        if (btn.text() === 'Select All') {
            checkboxes.prop('checked', true);
            btn.text('Deselect All');
        } else {
            checkboxes.prop('checked', false);
            btn.text('Select All');
        }
    });

    // 3. Reset Form on Modal Open for "Add Role"
    $('#add-role-btn').on('click', function() {
        $('#roleModalLabel').text('Add Role');
        $('#form-method').val('POST');
        $('#role-form')[0].reset();
        $('#role-id').val('');
        $('#name').prop('readonly', false);
        $('.select-module-all').text('Select All');
        $('.is-invalid').removeClass('is-invalid');
    });

    // 4. Submit Form (Save / Update Role)
    $('#role-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var roleId = $('#role-id').val();
        var url = roleId ? "{{ url('roles') }}/" + roleId : "{{ route('roles.store') }}";
        
        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            success: function(response) {
                hideLoader();
                $('#roleModal').modal('hide');
                table.ajax.reload();
                toastr.success(response.success);
            },
            error: function(xhr) {
                hideLoader();
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        $('#' + key).addClass('is-invalid');
                        $('#err-' + key).text(val[0]);
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong.');
                }
            }
        });
    });

    // 5. Edit Button Handler
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        showLoader();
        
        $.ajax({
            type: 'GET',
            url: "{{ url('roles') }}/" + id,
            success: function(response) {
                hideLoader();
                $('#roleModalLabel').text('Edit Role: ' + response.role.display_name);
                $('#form-method').val('PUT');
                $('#role-id').val(response.role.id);
                $('#display_name').val(response.role.display_name);
                $('#name').val(response.role.name);
                $('#description').val(response.role.description);
                
                // Readonly for default system roles to prevent breakages
                if (['super-admin', 'admin'].includes(response.role.name)) {
                    $('#name').prop('readonly', true);
                } else {
                    $('#name').prop('readonly', false);
                }

                // Uncheck all permissions first
                $('.perm-checkbox').prop('checked', false);
                
                // Check mapped permissions
                $.each(response.permissions, function(idx, val) {
                    $('#perm-' + val).prop('checked', true);
                });

                // Update "Select All" buttons label helper states
                $('.permission-group-card').each(function() {
                    var total = $(this).find('.perm-checkbox').length;
                    var checked = $(this).find('.perm-checkbox:checked').length;
                    var btn = $(this).find('.select-module-all');
                    if (total === checked && total > 0) {
                        btn.text('Deselect All');
                    } else {
                        btn.text('Select All');
                    }
                });
                
                $('.is-invalid').removeClass('is-invalid');
                $('#roleModal').modal('show');
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Failed to retrieve role details.');
            }
        });
    });

    // 6. Delete Button Handler
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This role will be deleted permanently. You can only delete roles with 0 active users.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoader();
                $.ajax({
                    type: 'DELETE',
                    url: "{{ url('roles') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error(xhr.responseJSON?.error || 'Failed to delete role.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
