@extends('layouts.app')

@section('title', 'Warehouse Management')
@section('module-title', 'Administration')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Warehouses</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-building me-2"></i>Warehouse Management</h4>
    @can('edit-settings')
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#warehouseModal" id="add-warehouse-btn">
        <i class="bi bi-plus-lg me-1"></i> Add Warehouse
    </button>
    @endcan
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        Registered Storage Outlets & Hubs
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="warehouses-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Warehouse Name</th>
                        <th>Code</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th width="120">Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Warehouse AJAX Modal -->
<div class="modal fade" id="warehouseModal" tabindex="-1" aria-labelledby="warehouseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="warehouseModalLabel">Add Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="warehouse-form" autocomplete="off">
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="warehouse-id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Main Outlet, Central Warehouse">
                        <div class="invalid-feedback" id="err-name"></div>
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">Warehouse Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control" required placeholder="e.g. WH-01, WH-HQ">
                        <div class="invalid-feedback" id="err-code"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Contact Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="e.g. +1 555-0100">
                        <div class="invalid-feedback" id="err-phone"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Physical Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter physical location..."></textarea>
                        <div class="invalid-feedback" id="err-address"></div>
                    </div>
                    <div class="form-check form-switch ps-5 mb-2">
                        <input type="checkbox" name="status" id="status" class="form-check-input" value="1" checked>
                        <label for="status" class="form-check-label">Active / Available for Transfers</label>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-warehouse-btn">Save Warehouse</button>
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
    var table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('warehouses.index') }}"
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'code'},
            {data: 'phone'},
            {data: 'address'},
            {data: 'status', orderable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']]
    });

    // 2. Reset form on add button click
    $('#add-warehouse-btn').on('click', function() {
        $('#warehouseModalLabel').text('Add Warehouse');
        $('#form-method').val('POST');
        $('#warehouse-form')[0].reset();
        $('#warehouse-id').val('');
        $('.is-invalid').removeClass('is-invalid');
    });

    // 3. Save / Update AJAX
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var id = $('#warehouse-id').val();
        var url = id ? "{{ url('warehouses') }}/" + id : "{{ route('warehouses.store') }}";
        
        var data = $(this).serialize();
        if (id) {
            data += '&_method=PUT';
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                hideLoader();
                $('#warehouseModal').modal('hide');
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
                    toastr.error(xhr.responseJSON?.error || 'Failed to save warehouse details.');
                }
            }
        });
    });

    // 4. Edit Warehouse details
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        showLoader();

        $.ajax({
            type: 'GET',
            url: "{{ url('warehouses') }}/" + id,
            success: function(response) {
                hideLoader();
                $('#warehouseModalLabel').text('Edit Warehouse: ' + response.name);
                $('#form-method').val('PUT');
                $('#warehouse-id').val(response.id);
                $('#name').val(response.name);
                $('#code').val(response.code);
                $('#phone').val(response.phone);
                $('#address').val(response.address);
                $('#status').prop('checked', response.status);
                $('.is-invalid').removeClass('is-invalid');
                $('#warehouseModal').modal('show');
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Failed to retrieve warehouse details.');
            }
        });
    });

    // 5. Toggle Status Switcher
    $(document).on('change', '.toggle-status', function() {
        var id = $(this).data('id');
        var checkbox = $(this);

        $.ajax({
            type: 'POST',
            url: "{{ url('warehouses') }}/" + id + "/toggle-status",
            success: function(response) {
                toastr.success(response.success);
            },
            error: function(xhr) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error('Failed to update status.');
            }
        });
    });

    // 6. Delete Warehouse
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This warehouse will be deleted and removed from transfers.",
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
                    url: "{{ url('warehouses') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error(xhr.responseJSON?.error || 'Failed to delete warehouse.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
