@extends('layouts.app')

@section('title', 'Attributes & Variants')
@section('module-title', 'Catalog')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Variants</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .color-preview-box {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Tabs Heading -->
        <ul class="nav nav-tabs mb-4" id="variantTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="colors-tab" data-bs-toggle="tab" data-bs-target="#colors-pane" type="button" role="tab" aria-controls="colors-pane" aria-selected="true">
                    <i class="bi bi-palette me-1"></i> Colors
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="storage-tab" data-bs-toggle="tab" data-bs-target="#storage-pane" type="button" role="tab" aria-controls="storage-pane" aria-selected="false">
                    <i class="bi bi-hdd me-1"></i> Storage Capacities
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ram-tab" data-bs-toggle="tab" data-bs-target="#ram-pane" type="button" role="tab" aria-controls="ram-pane" aria-selected="false">
                    <i class="bi bi-cpu me-1"></i> RAM Variants
                </button>
            </li>
        </ul>

        <!-- Tab Panes Content -->
        <div class="tab-content card p-4 shadow-sm" style="background-color: var(--bg-surface); border: 1px solid var(--border-color);">
            
            <!-- 1. COLORS TAB PANE -->
            <div class="tab-pane fade show active" id="colors-pane" role="tabpanel" aria-labelledby="colors-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">System Colors</h5>
                    @can('create-products')
                    <button class="btn btn-primary btn-sm" id="add-color-btn"><i class="bi bi-plus-lg me-1"></i> Add Color</button>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-custom w-100" id="colors-table">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Color Name</th>
                                <th>Hex Code</th>
                                <th>Preview</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- 2. STORAGE TAB PANE -->
            <div class="tab-pane fade" id="storage-pane" role="tabpanel" aria-labelledby="storage-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Storage Configurations</h5>
                    @can('create-products')
                    <button class="btn btn-primary btn-sm" id="add-storage-btn"><i class="bi bi-plus-lg me-1"></i> Add Storage</button>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-custom w-100" id="storage-table">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Capacity Size</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- 3. RAM TAB PANE -->
            <div class="tab-pane fade" id="ram-pane" role="tabpanel" aria-labelledby="ram-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">RAM Configurations</h5>
                    @can('create-products')
                    <button class="btn btn-primary btn-sm" id="add-ram-btn"><i class="bi bi-plus-lg me-1"></i> Add RAM</button>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-custom w-100" id="ram-table">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>RAM Size</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Color Modal -->
<div class="modal fade" id="colorModal" tabindex="-1" aria-labelledby="colorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="colorModalLabel">Add Color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="color-form" autocomplete="off">
                <input type="hidden" name="color_id" id="color-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="color_name" class="form-label">Color Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="color_name" class="form-control" required placeholder="e.g. Midnight Black, Titanium Silver">
                        <div class="invalid-feedback" id="err-color_name"></div>
                    </div>
                    <div class="mb-3">
                        <label for="color_code" class="form-label">Hex Code</label>
                        <div class="input-group">
                            <span class="input-group-text">#</span>
                            <input type="text" name="code" id="color_code" class="form-control" placeholder="e.g. 000000 or ffffff">
                        </div>
                        <small class="text-muted">Enter hex code (without #) to preview color background.</small>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Color</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Storage Modal -->
<div class="modal fade" id="storageModal" tabindex="-1" aria-labelledby="storageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="storageModalLabel">Add Storage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="storage-form" autocomplete="off">
                <input type="hidden" name="storage_id" id="storage-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="storage_value" class="form-label">Storage Capacity <span class="text-danger">*</span></label>
                        <input type="text" name="value" id="storage_value" class="form-control" required placeholder="e.g. 128GB, 256GB, 1TB">
                        <div class="invalid-feedback" id="err-storage_value"></div>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Storage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- RAM Modal -->
<div class="modal fade" id="ramModal" tabindex="-1" aria-labelledby="ramModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="ramModalLabel">Add RAM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="ram-form" autocomplete="off">
                <input type="hidden" name="ram_id" id="ram-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ram_value" class="form-label">RAM Size <span class="text-danger">*</span></label>
                        <input type="text" name="value" id="ram_value" class="form-control" required placeholder="e.g. 4GB, 8GB, 12GB, 16GB">
                        <div class="invalid-feedback" id="err-ram_value"></div>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save RAM</button>
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
    // 1. Colors Table
    var colorsTable = $('#colors-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('variants.index') }}",
            data: { type: 'colors' }
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'code', render: function(data) { return data ? '#' + data : 'N/A'; }},
            {data: 'code', orderable: false, render: function(data) {
                return data ? '<span class="color-preview-box" style="background-color: #' + data + '"></span>' : 'N/A';
            }},
            {data: null, orderable: false, render: function(data) {
                return '<div class="btn-group btn-group-sm">' +
                    '<button class="btn btn-outline-primary edit-color-btn" data-id="' + data.id + '"><i class="bi bi-pencil"></i></button>' +
                    '<button class="btn btn-outline-danger delete-color-btn" data-id="' + data.id + '"><i class="bi bi-trash"></i></button>' +
                '</div>';
            }}
        ]
    });

    // 2. Storage Table
    var storageTable = $('#storage-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('variants.index') }}",
            data: { type: 'storage' }
        },
        columns: [
            {data: 'id'},
            {data: 'value'},
            {data: null, orderable: false, render: function(data) {
                return '<div class="btn-group btn-group-sm">' +
                    '<button class="btn btn-outline-primary edit-storage-btn" data-id="' + data.id + '"><i class="bi bi-pencil"></i></button>' +
                    '<button class="btn btn-outline-danger delete-storage-btn" data-id="' + data.id + '"><i class="bi bi-trash"></i></button>' +
                '</div>';
            }}
        ]
    });

    // 3. RAM Table
    var ramTable = $('#ram-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('variants.index') }}",
            data: { type: 'ram' }
        },
        columns: [
            {data: 'id'},
            {data: 'value'},
            {data: null, orderable: false, render: function(data) {
                return '<div class="btn-group btn-group-sm">' +
                    '<button class="btn btn-outline-primary edit-ram-btn" data-id="' + data.id + '"><i class="bi bi-pencil"></i></button>' +
                    '<button class="btn btn-outline-danger delete-ram-btn" data-id="' + data.id + '"><i class="bi bi-trash"></i></button>' +
                '</div>';
            }}
        ]
    });

    // Reload active tab on click
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var targetId = $(e.target).attr('id');
        if (targetId === 'colors-tab') colorsTable.ajax.reload();
        if (targetId === 'storage-tab') storageTable.ajax.reload();
        if (targetId === 'ram-tab') ramTable.ajax.reload();
    });

    // ==========================================
    // COLORS AJAX
    // ==========================================
    $('#add-color-btn').on('click', function() {
        $('#colorModalLabel').text('Add Color');
        $('#color-form')[0].reset();
        $('#color-id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('#colorModal').modal('show');
    });

    $('#color-form').on('submit', function(e) {
        e.preventDefault();
        showLoader();
        var id = $('#color-id').val();
        var url = id ? "{{ url('variants/colors') }}/" + id : "{{ route('variants.colors.store') }}";
        var data = $(this).serialize();
        if (id) data += '&_method=PUT';

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(res) {
                hideLoader();
                $('#colorModal').modal('hide');
                colorsTable.ajax.reload();
                toastr.success(res.success);
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Operation failed.');
            }
        });
    });

    $(document).on('click', '.edit-color-btn', function() {
        var id = $(this).data('id');
        showLoader();
        $.ajax({
            url: "{{ url('variants/colors') }}/" + id,
            success: function(res) {
                hideLoader();
                $('#colorModalLabel').text('Edit Color');
                $('#color-id').val(res.id);
                $('#color_name').val(res.name);
                $('#color_code').val(res.code);
                $('#colorModal').modal('show');
            }
        });
    });

    $(document).on('click', '.delete-color-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: "{{ url('variants/colors') }}/" + id,
                    success: function(res) { colorsTable.ajax.reload(); toastr.success(res.success); },
                    error: function(xhr) { toastr.error(xhr.responseJSON?.error); }
                });
            }
        });
    });

    // ==========================================
    // STORAGE AJAX
    // ==========================================
    $('#add-storage-btn').on('click', function() {
        $('#storageModalLabel').text('Add Storage');
        $('#storage-form')[0].reset();
        $('#storage-id').val('');
        $('#storageModal').modal('show');
    });

    $('#storage-form').on('submit', function(e) {
        e.preventDefault();
        showLoader();
        var id = $('#storage-id').val();
        var url = id ? "{{ url('variants/storage') }}/" + id : "{{ route('variants.storage.store') }}";
        var data = $(this).serialize();
        if (id) data += '&_method=PUT';

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(res) {
                hideLoader();
                $('#storageModal').modal('hide');
                storageTable.ajax.reload();
                toastr.success(res.success);
            },
            error: function() { hideLoader(); }
        });
    });

    $(document).on('click', '.edit-storage-btn', function() {
        var id = $(this).data('id');
        showLoader();
        $.ajax({
            url: "{{ url('variants/storage') }}/" + id,
            success: function(res) {
                hideLoader();
                $('#storageModalLabel').text('Edit Storage');
                $('#storage-id').val(res.id);
                $('#storage_value').val(res.value);
                $('#storageModal').modal('show');
            }
        });
    });

    $(document).on('click', '.delete-storage-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: "{{ url('variants/storage') }}/" + id,
                    success: function(res) { storageTable.ajax.reload(); toastr.success(res.success); },
                    error: function(xhr) { toastr.error(xhr.responseJSON?.error); }
                });
            }
        });
    });

    // ==========================================
    // RAM AJAX
    // ==========================================
    $('#add-ram-btn').on('click', function() {
        $('#ramModalLabel').text('Add RAM');
        $('#ram-form')[0].reset();
        $('#ram-id').val('');
        $('#ramModal').modal('show');
    });

    $('#ram-form').on('submit', function(e) {
        e.preventDefault();
        showLoader();
        var id = $('#ram-id').val();
        var url = id ? "{{ url('variants/ram') }}/" + id : "{{ route('variants.ram.store') }}";
        var data = $(this).serialize();
        if (id) data += '&_method=PUT';

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(res) {
                hideLoader();
                $('#ramModal').modal('hide');
                ramTable.ajax.reload();
                toastr.success(res.success);
            },
            error: function() { hideLoader(); }
        });
    });

    $(document).on('click', '.edit-ram-btn', function() {
        var id = $(this).data('id');
        showLoader();
        $.ajax({
            url: "{{ url('variants/ram') }}/" + id,
            success: function(res) {
                hideLoader();
                $('#ramModalLabel').text('Edit RAM');
                $('#ram-id').val(res.id);
                $('#ram_value').val(res.value);
                $('#ramModal').modal('show');
            }
        });
    });

    $(document).on('click', '.delete-ram-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: "{{ url('variants/ram') }}/" + id,
                    success: function(res) { ramTable.ajax.reload(); toastr.success(res.success); },
                    error: function(xhr) { toastr.error(xhr.responseJSON?.error); }
                });
            }
        });
    });
});
</script>
@endpush
