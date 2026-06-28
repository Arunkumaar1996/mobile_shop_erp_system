@extends('layouts.app')

@section('title', 'Warehouse Stock Inventory')
@section('module-title', 'Administration')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}" class="text-decoration-none">Warehouses</a></li>
<li class="breadcrumb-item active" aria-current="page">Stock Explorer</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold"><i class="bi bi-boxes me-2"></i>Stock Inventory: {{ $warehouse->name }}</h4>
    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Warehouses
    </a>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        Available items in [{{ $warehouse->code }}] {{ $warehouse->name }}
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="warehouse-stocks-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Variant</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th width="150" class="text-end">Quantity in Stock</th>
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
    $('#warehouse-stocks-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('warehouses.stocks', $warehouse->id) }}"
        },
        columns: [
            {data: 'sku'},
            {data: 'product'},
            {data: 'brand'},
            {data: 'category'},
            {data: 'quantity', className: 'text-end'}
        ],
        order: [[4, 'desc']]
    });
});
</script>
@endpush
