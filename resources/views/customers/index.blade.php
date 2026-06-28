@extends('layouts.app')

@section('title', 'Customers Directory')
@section('module-title', 'Contacts')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Customers</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-person-heart me-2"></i>Customers Registry</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.export') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        <a href="{{ route('customers.print') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
            <i class="bi bi-printer me-1"></i> Print List
        </a>
        @can('manage-contacts')
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#customerModal" id="add-customer-btn">
            <i class="bi bi-plus-lg me-1"></i> Add Customer
        </button>
        @endcan
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        Retail Clients & Customers
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="customers-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Wallet Balance</th>
                        <th>Loyalty Points</th>
                        <th width="100">Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Customer AJAX Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="customerModalLabel">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="customer-form" autocomplete="off">
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="customer-id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. John Doe">
                        <div class="invalid-feedback" id="err-name"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control" required placeholder="e.g. +1 555-0199">
                        <div class="invalid-feedback" id="err-phone"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="e.g. john@example.com">
                        <div class="invalid-feedback" id="err-email"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Residential/Delivery Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter full address..."></textarea>
                        <div class="invalid-feedback" id="err-address"></div>
                    </div>
                    <div class="form-check form-switch ps-5 mb-2">
                        <input type="checkbox" name="status" id="status" class="form-check-input" value="1" checked>
                        <label for="status" class="form-check-label">Active (Allows Billing)</label>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-customer-btn">Save Customer</button>
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
    var table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customers.index') }}"
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'phone'},
            {data: 'email'},
            {data: 'wallet_balance'},
            {data: 'loyalty_points'},
            {data: 'status', orderable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']]
    });

    // 2. Reset form on add button click
    $('#add-customer-btn').on('click', function() {
        $('#customerModalLabel').text('Add Customer');
        $('#form-method').val('POST');
        $('#customer-form')[0].reset();
        $('#customer-id').val('');
        $('.is-invalid').removeClass('is-invalid');
    });

    // 3. Save / Update AJAX
    $('#customer-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var id = $('#customer-id').val();
        var url = id ? "{{ url('customers') }}/" + id : "{{ route('customers.store') }}";
        
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
                $('#customerModal').modal('hide');
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
                    toastr.error('Failed to save customer details.');
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
            url: "{{ url('customers') }}/" + id,
            success: function(response) {
                hideLoader();
                $('#customerModalLabel').text('Edit Customer: ' + response.name);
                $('#form-method').val('PUT');
                $('#customer-id').val(response.id);
                $('#name').val(response.name);
                $('#phone').val(response.phone);
                $('#email').val(response.email);
                $('#address').val(response.address);
                $('#status').prop('checked', response.status);
                $('.is-invalid').removeClass('is-invalid');
                $('#customerModal').modal('show');
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Failed to retrieve customer details.');
            }
        });
    });

    // 5. Toggle Status Switcher
    $(document).on('change', '.toggle-status', function() {
        var id = $(this).data('id');
        var checkbox = $(this);

        $.ajax({
            type: 'POST',
            url: "{{ url('customers') }}/" + id + "/toggle-status",
            success: function(response) {
                toastr.success(response.success);
            },
            error: function(xhr) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error('Failed to update customer status.');
            }
        });
    });

    // 6. Delete Customer
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This customer record will be soft-deleted.",
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
                    url: "{{ url('customers') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error('Failed to delete customer.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
