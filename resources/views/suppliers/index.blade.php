@extends('layouts.app')

@section('title', 'Suppliers Directory')
@section('module-title', 'Contacts')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Suppliers</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-truck me-2"></i>Suppliers Registry</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('suppliers.export') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        <a href="{{ route('suppliers.print') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
            <i class="bi bi-printer me-1"></i> Print List
        </a>
        @can('manage-contacts')
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#supplierModal" id="add-supplier-btn">
            <i class="bi bi-plus-lg me-1"></i> Add Supplier
        </button>
        @endcan
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        Wholesalers & Device Suppliers
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="suppliers-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Supplier / contact</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>GSTIN / Tax ID</th>
                        <th>Outstanding Balance</th>
                        <th width="100">Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Supplier AJAX Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="supplierModalLabel">Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="supplier-form" autocomplete="off">
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="supplier-id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Supplier/Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Apple Distributors Ltd">
                        <div class="invalid-feedback" id="err-name"></div>
                    </div>
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person" class="form-control" placeholder="e.g. Robert Downey">
                        <div class="invalid-feedback" id="err-contact_person"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control" required placeholder="e.g. +1 555-0188">
                        <div class="invalid-feedback" id="err-phone"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="e.g. vendor@apple-dist.com">
                        <div class="invalid-feedback" id="err-email"></div>
                    </div>
                    <div class="mb-3">
                        <label for="gstin" class="form-label">GSTIN / Tax ID</label>
                        <input type="text" name="gstin" id="gstin" class="form-control" placeholder="e.g. 07AAAAA1111A1Z1">
                        <div class="invalid-feedback" id="err-gstin"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Warehouse/Office Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter supplier address..."></textarea>
                        <div class="invalid-feedback" id="err-address"></div>
                    </div>
                    <div class="form-check form-switch ps-5 mb-2">
                        <input type="checkbox" name="status" id="status" class="form-check-input" value="1" checked>
                        <label for="status" class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-supplier-btn">Save Supplier</button>
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
    var table = $('#suppliers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('suppliers.index') }}"
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'phone'},
            {data: 'email'},
            {data: 'gstin'},
            {data: 'outstanding_balance'},
            {data: 'status', orderable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']]
    });

    // 2. Reset form on add button click
    $('#add-supplier-btn').on('click', function() {
        $('#supplierModalLabel').text('Add Supplier');
        $('#form-method').val('POST');
        $('#supplier-form')[0].reset();
        $('#supplier-id').val('');
        $('.is-invalid').removeClass('is-invalid');
    });

    // 3. Save / Update AJAX
    $('#supplier-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var id = $('#supplier-id').val();
        var url = id ? "{{ url('suppliers') }}/" + id : "{{ route('suppliers.store') }}";
        
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
                $('#supplierModal').modal('hide');
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
                    toastr.error('Failed to save supplier details.');
                }
            }
        });
    });

    // 4. Edit details loader
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        showLoader();

        $.ajax({
            type: 'GET',
            url: "{{ url('suppliers') }}/" + id,
            success: function(response) {
                hideLoader();
                $('#supplierModalLabel').text('Edit Supplier: ' + response.name);
                $('#form-method').val('PUT');
                $('#supplier-id').val(response.id);
                $('#name').val(response.name);
                $('#contact_person').val(response.contact_person);
                $('#phone').val(response.phone);
                $('#email').val(response.email);
                $('#gstin').val(response.gstin);
                $('#address').val(response.address);
                $('#status').prop('checked', response.status);
                $('.is-invalid').removeClass('is-invalid');
                $('#supplierModal').modal('show');
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Failed to retrieve supplier details.');
            }
        });
    });

    // 5. Toggle Status Switcher
    $(document).on('change', '.toggle-status', function() {
        var id = $(this).data('id');
        var checkbox = $(this);

        $.ajax({
            type: 'POST',
            url: "{{ url('suppliers') }}/" + id + "/toggle-status",
            success: function(response) {
                toastr.success(response.success);
            },
            error: function(xhr) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error('Failed to update supplier status.');
            }
        });
    });

    // 6. Delete Supplier
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This supplier record will be soft-deleted.",
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
                    url: "{{ url('suppliers') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error('Failed to delete supplier.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
