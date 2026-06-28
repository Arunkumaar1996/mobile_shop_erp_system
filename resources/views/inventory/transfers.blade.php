@extends('layouts.app')

@section('title', 'Warehouse Stock Transfers')
@section('module-title', 'Operations')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
<li class="breadcrumb-item active" aria-current="page">Transfers</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="row">
    <!-- Left Column: Form -->
    <div class="col-lg-4">
        <h4 class="mb-3 fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Initiate Transfer</h4>
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="transfer-form" autocomplete="off">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="from_warehouse_id" class="form-label">Source Warehouse <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" id="from_warehouse_id" class="form-select form-select-sm" required>
                            <option value="">Select Source</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="to_warehouse_id" class="form-label">Destination Warehouse <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" id="to_warehouse_id" class="form-select form-select-sm" required>
                            <option value="">Select Destination</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="product_variant_id" class="form-label">Product Variant <span class="text-danger">*</span></label>
                        <select name="product_variant_id" id="product_variant_id" class="form-select form-select-sm" required>
                            <option value="">Select Product Variant</option>
                            @foreach($variants as $v)
                                @php
                                    $attributes = [];
                                    if ($v->color) $attributes[] = $v->color->name;
                                    if ($v->storageVariant) $attributes[] = $v->storageVariant->value;
                                    if ($v->ramVariant) $attributes[] = $v->ramVariant->value;
                                    $attrStr = count($attributes) > 0 ? ' (' . implode(' / ', $attributes) . ')' : '';
                                @endphp
                                <option value="{{ $v->id }}" data-imei-tracked="{{ $v->product->is_imei_tracked ? 1 : 0 }}">
                                    [{{ $v->sku }}] {{ $v->product->name }}{{ $attrStr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity to Transfer <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control form-control-sm" required min="1" value="1">
                    </div>

                    <!-- Dynamic IMEI Select (For tracked devices) -->
                    <div id="imei-inputs-container" class="d-none border border-color rounded p-3 mb-3" style="background-color: var(--bg-body);">
                        <h6 class="fw-bold mb-2 text-info"><i class="bi bi-upc-scan me-1"></i>Select IMEIs to Relocate</h6>
                        <div id="imei-list-checkboxes" style="max-height: 150px; overflow-y: auto;">
                            <!-- Checkboxes will render dynamically -->
                        </div>
                        <small class="text-muted d-block mt-2">Select exactly <span id="required-imeis-count" class="fw-bold text-info">1</span> IMEIs.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-arrow-left-right me-1"></i>Execute Transfer</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: History -->
    <div class="col-lg-8">
        <h4 class="mb-3 fw-bold"><i class="bi bi-clock-history me-2"></i>Transfer Log Audit</h4>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom w-100" id="transfers-history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Warehouse Location</th>
                                <th>Product Variant [SKU]</th>
                                <th>Movement type</th>
                                <th>Quantity</th>
                                <th>Ledger Action</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
    // 1. Initialize History Table
    var table = $('#transfers-history-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('inventory.transfers') }}"
        },
        columns: [
            {data: 'date'},
            {data: 'warehouse'},
            {data: 'product'},
            {data: 'type'},
            {data: 'quantity'},
            {data: 'reference'},
            {data: 'user'}
        ],
        order: [[0, 'desc']]
    });

    // 2. Fetch Source Warehouse available IMEIs for checkboxes list
    function refreshImeiCheckboxes() {
        var option = $('#product_variant_id option:selected');
        var isImeiTracked = option.data('imei-tracked') == 1;
        var qty = parseInt($('#quantity').val()) || 0;
        var container = $('#imei-inputs-container');
        var list = $('#imei-list-checkboxes');
        
        $('#required-imeis-count').text(qty);

        list.empty();
        if (isImeiTracked && qty > 0) {
            var fromWh = $('#from_warehouse_id').val();
            var variantId = $('#product_variant_id').val();

            if (!fromWh) {
                list.append('<span class="text-danger small">Select source warehouse first.</span>');
                container.removeClass('d-none');
                return;
            }

            showLoader();
            $.ajax({
                url: "{{ route('inventory.available-imeis') }}",
                data: { product_variant_id: variantId, warehouse_id: fromWh },
                success: function(res) {
                    hideLoader();
                    container.removeClass('d-none');
                    if (res.length === 0) {
                        list.append('<span class="text-danger small">No available IMEIs in this source warehouse.</span>');
                        return;
                    }

                    $.each(res, function(idx, item) {
                        list.append(`
                        <div class="form-check">
                            <input class="form-check-input imei-checkbox" type="checkbox" name="imeis[]" value="${item.imei}" id="imei_${item.id}">
                            <label class="form-check-label text-sm" for="imei_${item.id}">
                                ${item.imei}
                            </label>
                        </div>`);
                    });
                },
                error: function() { hideLoader(); }
            });
        } else {
            container.addClass('d-none');
        }
    }

    $('#from_warehouse_id, #product_variant_id, #quantity').on('change input', function() {
        refreshImeiCheckboxes();
    });

    // 3. Form Submit with validation
    $('#transfer-form').on('submit', function(e) {
        e.preventDefault();
        
        var option = $('#product_variant_id option:selected');
        var isImeiTracked = option.data('imei-tracked') == 1;
        var qty = parseInt($('#quantity').val()) || 0;

        if (isImeiTracked) {
            var selectedCount = $('.imei-checkbox:checked').length;
            if (selectedCount !== qty) {
                toastr.warning('Please select exactly ' + qty + ' IMEIs to transfer (Currently selected: ' + selectedCount + ').');
                return;
            }
        }

        showLoader();
        $.ajax({
            type: 'POST',
            url: "{{ route('inventory.transfers.store') }}",
            data: $(this).serialize(),
            success: function(res) {
                hideLoader();
                toastr.success(res.success);
                $('#transfer-form')[0].reset();
                $('#imei-inputs-container').addClass('d-none');
                table.ajax.reload();
            },
            error: function(xhr) {
                hideLoader();
                toastr.error(xhr.responseJSON?.error || 'Transfer failed. Check stock availability.');
            }
        });
    });
});
</script>
@endpush
