@extends('layouts.app')

@section('title', 'Global Inventory')
@section('module-title', 'Operations')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Inventory</li>
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
    <h4 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Global Inventory Logs</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.transfers') }}" class="btn btn-outline-info btn-sm">
            <i class="bi bi-arrow-left-right me-1"></i> Stock Transfers
        </a>
        <a href="{{ route('inventory.adjustments') }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-sliders me-1"></i> Stock Adjustments
        </a>
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="filter-warehouse" class="form-label text-xs fw-bold text-muted uppercase">Warehouse Location</label>
            <select id="filter-warehouse" class="form-select form-select-sm">
                <option value="">All Warehouses</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm">
    <div class="card-header">
        Current Consolidated Stock Ledger
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom w-100" id="global-inventory-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>SKU</th>
                        <th>Product Variant Config</th>
                        <th>Warehouse</th>
                        <th>Qty in Stock</th>
                        <th>Alert Alarm</th>
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
    var table = $('#global-inventory-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('inventory.index') }}",
            data: function(d) {
                d.warehouse_id = $('#filter-warehouse').val();
            }
        },
        columns: [
            {data: 'id'},
            {data: 'sku'},
            {data: 'product'},
            {data: 'warehouse'},
            {data: 'quantity'},
            {data: 'alert_status', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']]
    });

    $('#filter-warehouse').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@endpush
