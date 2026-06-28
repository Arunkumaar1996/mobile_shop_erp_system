@extends('layouts.app')

@section('title', 'Users Management')
@section('module-title', 'Administration')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Users</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .filters-bar {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Users Registry</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('users.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i> Print</a>
        <a href="{{ route('users.export') }}" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel</a>
        @can('create-users')
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" id="add-user-btn">
            <i class="bi bi-person-plus me-1"></i> Add New User
        </button>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <form id="filter-form" class="row g-3">
        <div class="col-md-4">
            <label for="filter-branch" class="form-label text-xs fw-bold text-muted uppercase">Branch</label>
            <select id="filter-branch" class="form-select form-select-sm">
                <option value="">All Branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="filter-status" class="form-label text-xs fw-bold text-muted uppercase">Status</label>
            <select id="filter-status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="1">Active</option>
                <option value="0">Suspended</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="button" id="reset-filters" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
        </div>
    </form>
</div>

<!-- Bulk Actions & Table Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>User Records</span>
        @can('delete-users')
        <button id="bulk-delete-btn" class="btn btn-danger btn-sm d-none"><i class="bi bi-trash me-1"></i> Delete Selected</button>
        @endcan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="users-table">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Branch</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- User AJAX Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="userModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="user-form" autocomplete="off">
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="user-id">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. John Doe">
                            <div class="invalid-feedback" id="err-name"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="form-control" required placeholder="e.g. johndoe">
                            <div class="invalid-feedback" id="err-username"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="e.g. john@example.com">
                            <div class="invalid-feedback" id="err-email"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control" placeholder="e.g. +123456789">
                            <div class="invalid-feedback" id="err-phone"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-branch_id"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label">System Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->display_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-role_id"></div>
                        </div>
                        <div class="col-md-12">
                            <label for="password" class="form-label">Password <span class="text-danger" id="pw-required-star">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 8 characters">
                            <div class="invalid-feedback" id="err-password"></div>
                            <small class="text-muted d-none" id="pw-help">Leave password blank if you do not want to change it.</small>
                        </div>
                        <div class="col-md-12 form-check form-switch ps-5">
                            <input type="checkbox" name="status" id="status" class="form-check-input" value="1" checked>
                            <label for="status" class="form-check-label">Active / Allow Portal Login</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-user-btn">Save User</button>
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
    var table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('users.index') }}",
            data: function (d) {
                d.branch_id = $('#filter-branch').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            {data: 'checkbox', orderable: false, searchable: false},
            {data: 'id'},
            {data: 'name'},
            {data: 'email'},
            {data: 'phone'},
            {data: 'branch'},
            {data: 'role', orderable: false},
            {data: 'status', orderable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[1, 'desc']],
        pageLength: 10
    });

    // 2. Filter events
    $('#filter-branch, #filter-status').on('change', function() {
        table.ajax.reload();
    });

    $('#reset-filters').on('click', function() {
        $('#filter-form')[0].reset();
        table.ajax.reload();
    });

    // 3. Selection and Bulk Action UI
    $('#select-all').on('click', function() {
        var checked = this.checked;
        $('.select-row').prop('checked', checked);
        toggleBulkBtn();
    });

    $(document).on('change', '.select-row', function() {
        toggleBulkBtn();
    });

    function toggleBulkBtn() {
        var selectedCount = $('.select-row:checked').length;
        if (selectedCount > 0) {
            $('#bulk-delete-btn').removeClass('d-none').text('Delete Selected (' + selectedCount + ')');
        } else {
            $('#bulk-delete-btn').addClass('d-none');
        }
    }

    // 4. Reset Form on Modal Open for "Add User"
    $('#add-user-btn').on('click', function() {
        $('#userModalLabel').text('Add User');
        $('#form-method').val('POST');
        $('#user-form')[0].reset();
        $('#user-id').val('');
        $('#pw-required-star').removeClass('d-none');
        $('#password').prop('required', true);
        $('#pw-help').addClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
    });

    // 5. Submit Form (Save / Update User)
    $('#user-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var userId = $('#user-id').val();
        var url = userId ? "{{ url('users') }}/" + userId : "{{ route('users.store') }}";
        
        // Prepare data. Switch values require manual adjustment for serialize
        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            success: function(response) {
                hideLoader();
                $('#userModal').modal('hide');
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
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
                }
            }
        });
    });

    // 6. Edit Button Handler
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        showLoader();
        
        $.ajax({
            type: 'GET',
            url: "{{ url('users') }}/" + id,
            success: function(response) {
                hideLoader();
                $('#userModalLabel').text('Edit User');
                $('#form-method').val('PUT');
                $('#user-id').val(response.user.id);
                $('#name').val(response.user.name);
                $('#username').val(response.user.username);
                $('#email').val(response.user.email);
                $('#phone').val(response.user.phone);
                $('#branch_id').val(response.user.branch_id);
                $('#role_id').val(response.role_id);
                $('#status').prop('checked', response.user.status);
                
                $('#pw-required-star').addClass('d-none');
                $('#password').prop('required', false).val('');
                $('#pw-help').removeClass('d-none');
                
                $('.is-invalid').removeClass('is-invalid');
                $('#userModal').modal('show');
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Failed to retrieve user details.');
            }
        });
    });

    // 7. Toggle Status (Inline Checkbox Switch)
    $(document).on('change', '.toggle-status', function() {
        var id = $(this).data('id');
        var checkbox = $(this);
        
        $.ajax({
            type: 'POST',
            url: "{{ url('users') }}/" + id + "/toggle-status",
            success: function(response) {
                toastr.success(response.success);
            },
            error: function(xhr) {
                // Revert checkbox state
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error(xhr.responseJSON?.error || 'Failed to update user status.');
            }
        });
    });

    // 8. Delete Button Handler
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This user will be soft-deleted and cannot access the portal.",
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
                    url: "{{ url('users') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error(xhr.responseJSON?.error || 'Failed to delete user.');
                    }
                });
            }
        });
    });

    // 9. Bulk Delete Handler
    $('#bulk-delete-btn').on('click', function() {
        var selectedIds = [];
        $('.select-row:checked').each(function() {
            selectedIds.push($(this).val());
        });

        Swal.fire({
            title: 'Are you sure?',
            text: "This will bulk soft-delete all selected users (" + selectedIds.length + " accounts).",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, bulk delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoader();
                $.ajax({
                    type: 'POST',
                    url: "{{ route('users.bulk-delete') }}",
                    data: { ids: selectedIds },
                    success: function(response) {
                        hideLoader();
                        table.ajax.reload();
                        $('#select-all').prop('checked', false);
                        $('#bulk-delete-btn').addClass('d-none');
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error(xhr.responseJSON?.error || 'Bulk delete operation failed.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
