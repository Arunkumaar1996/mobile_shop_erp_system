@extends('layouts.app')

@section('title', 'Product Catalog')
@section('module-title', 'Catalog')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Products</li>
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
    <h4 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Product Catalog</h4>
    <div class="d-flex gap-2">
        @can('create-products')
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <form id="filter-form" class="row g-3">
        <div class="col-md-4">
            <label for="filter-brand" class="form-label text-xs fw-bold text-muted uppercase">Brand</label>
            <select id="filter-brand" class="form-select form-select-sm">
                <option value="">All Brands</option>
                @foreach($brands as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="filter-category" class="form-label text-xs fw-bold text-muted uppercase">Category</label>
            <select id="filter-category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="button" id="reset-filters" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        Registered Products & Devices
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="products-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Product / Model</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Variant Options</th>
                        <th>IMEI Tracked</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('products.index') }}",
            data: function (d) {
                d.brand_id = $('#filter-brand').val();
                d.category_id = $('#filter-category').val();
            }
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'brand'},
            {data: 'category'},
            {data: 'variants_count', searchable: false},
            {data: 'imei_tracked', orderable: false, searchable: false},
            {data: 'status', orderable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']]
    });

    // 2. Filter actions
    $('#filter-brand, #filter-category').on('change', function() {
        table.ajax.reload();
    });

    $('#reset-filters').on('click', function() {
        $('#filter-form')[0].reset();
        table.ajax.reload();
    });

    // 3. Toggle Status Switcher
    $(document).on('change', '.toggle-status', function() {
        var id = $(this).data('id');
        var checkbox = $(this);

        $.ajax({
            type: 'POST',
            url: "{{ url('products') }}/" + id + "/toggle-status",
            success: function(response) {
                toastr.success(response.success);
            },
            error: function(xhr) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error('Failed to update product status.');
            }
        });
    });

    // 4. Delete Product catalog
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This will soft-delete the product and all associated variants.",
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
                    url: "{{ url('products') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error(xhr.responseJSON?.error || 'Failed to delete product.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
