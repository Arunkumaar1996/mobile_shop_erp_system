@extends('layouts.app')

@section('title', 'Stock Adjustments')
@section('module-title', 'Operations')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
<li class="breadcrumb-item active" aria-current="page">Adjustments</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="row">
    <!-- Left Column: Form -->
    <div class="col-lg-4">
        <h4 class="mb-3 fw-bold"><i class="bi bi-sliders me-2"></i>Record Adjustment</h4>
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="adjustment-form" autocomplete="off">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select form-select-sm" required>
                            <option value="">Select Warehouse</option>
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
                        <label for="type" class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select form-select-sm" required>
                            <option value="in">Addition (+) - e.g. Found Stock</option>
                            <option value="out">Reduction (-) - e.g. Damage / Write-off</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control form-control-sm" required min="1" value="1">
                    </div>

                    <!-- Dynamic IMEI Inputs (Shown only if variant is IMEI tracked) -->
                    <div id="imei-inputs-container" class="d-none border border-color rounded p-3 mb-3" style="background-color: var(--bg-body);">
                        <h6 class="fw-bold mb-2 text-warning"><i class="bi bi-upc-scan me-1"></i>Tracked IMEIs Required</h6>
                        <div id="imei-inputs"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-check-lg me-1"></i>Save Adjustment</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Audit Logs -->
    <div class="col-lg-8">
        <h4 class="mb-3 fw-bold"><i class="bi bi-journal-text me-2"></i>Adjustment History Audit</h4>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom w-100" id="adjustments-history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Warehouse</th>
                                <th>Product [SKU]</th>
                                <th>Adjustment</th>
                                <th>Qty</th>
                                <th>Old Qty</th>
                                <th>New Qty</th>
                                <th>User</th>
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
    // 1. Initialize Table
    var table = $('#adjustments-history-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('inventory.adjustments') }}"
        },
        columns: [
            {data: 'date'},
            {data: 'warehouse'},
            {data: 'product'},
            {data: 'type'},
            {data: 'quantity'},
            {data: 'old_qty'},
            {data: 'new_qty'},
            {data: 'user'}
        ],
        order: [[0, 'desc']]
    });

    // 2. IMEI Fields Generation
    function refreshImeiFields() {
        var option = $('#product_variant_id option:selected');
        var isImeiTracked = option.data('imei-tracked') == 1;
        var qty = parseInt($('#quantity').val()) || 0;
        var container = $('#imei-inputs-container');
        var list = $('#imei-inputs');

        list.empty();
        if (isImeiTracked && qty > 0) {
            container.removeClass('d-none');
            var isReduction = $('#type').val() === 'out';
            
            if (isReduction) {
                // If it is a reduction, fetch available IMEIs from database as a select dropdown list
                var warehouseId = $('#warehouse_id').val();
                var variantId = $('#product_variant_id').val();
                
                if (!warehouseId) {
                    list.append('<span class="text-danger small">Select a warehouse first to fetch available IMEIs.</span>');
                    return;
                }
                
                // Render dropdowns
                showLoader();
                $.ajax({
                    url: "{{ route('inventory.available-imeis') }}",
                    data: { product_variant_id: variantId, warehouse_id: warehouseId },
                    success: function(res) {
                        hideLoader();
                        if (res.length === 0) {
                            list.append('<span class="text-danger small">No available IMEIs found in this warehouse.</span>');
                            return;
                        }
                        
                        for (var i = 0; i < qty; i++) {
                            var options = '<option value="">Select IMEI</option>';
                            $.each(res, function(idx, item) {
                                options += '<option value="' + item.imei + '">' + item.imei + '</option>';
                            });
                            
                            list.append(`
                            <div class="mb-2">
                                <label class="form-label text-xs">IMEI Number #${i+1}</label>
                                <select name="imeis[]" class="form-select form-select-sm" required>
                                    ${options}
                                </select>
                            </div>`);
                        }
                    },
                    error: function() { hideLoader(); }
                });
            } else {
                // If addition, show simple textboxes for manual entry
                for (var i = 0; i < qty; i++) {
                    list.append(`
                    <div class="mb-2">
                        <label class="form-label text-xs">IMEI Number #${i+1}</label>
                        <input type="text" name="imeis[]" class="form-control form-control-sm" required placeholder="Enter 15-digit IMEI">
                    </div>`);
                }
            }
        } else {
            container.addClass('d-none');
        }
    }

    $('#product_variant_id, #quantity, #type, #warehouse_id').on('change input', function() {
        refreshImeiFields();
    });

    // 3. Form Submit
    $('#adjustment-form').on('submit', function(e) {
        e.preventDefault();
        showLoader();

        $.ajax({
            type: 'POST',
            url: "{{ route('inventory.adjustments.store') }}",
            data: $(this).serialize(),
            success: function(res) {
                hideLoader();
                toastr.success(res.success);
                $('#adjustment-form')[0].reset();
                $('#imei-inputs-container').addClass('d-none');
                table.ajax.reload();
            },
            error: function(xhr) {
                hideLoader();
                toastr.error(xhr.responseJSON?.error || 'Failed to save adjustment.');
            }
        });
    });
});
</script>
@endpush
