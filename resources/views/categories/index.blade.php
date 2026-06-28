@extends('layouts.app')

@section('title', 'Categories & Subcategories')
@section('module-title', 'Catalog')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Categories</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="row">
    <!-- Left Column: Categories -->
    <div class="col-xl-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 fw-bold"><i class="bi bi-tags me-2"></i>Categories</h4>
            @can('create-products')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" id="add-category-btn">
                <i class="bi bi-plus-lg me-1"></i> Add Category
            </button>
            @endcan
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom w-100" id="categories-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>Category Name</th>
                                <th>Subcategories</th>
                                <th width="100">Status</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Subcategories -->
    <div class="col-xl-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 fw-bold"><i class="bi bi-tag-fill me-2"></i>Subcategories</h4>
            @can('create-products')
            <button class="btn btn-outline-primary btn-sm" id="add-subcategory-btn">
                <i class="bi bi-plus-lg me-1"></i> Add Subcategory
            </button>
            @endcan
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom w-100" id="subcategories-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>Parent Category</th>
                                <th>Subcategory Name</th>
                                <th width="100">Status</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="categoryModalLabel">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="category-form" autocomplete="off">
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="category-id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Smart Phones, Accessories, Tablets">
                        <div class="invalid-feedback" id="err-name"></div>
                    </div>
                    <div class="form-check form-switch ps-5 mb-2">
                        <input type="checkbox" name="status" id="status" class="form-check-input" value="1" checked>
                        <label for="status" class="form-check-label">Active / Visible in Forms</label>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-category-btn">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Subcategory Modal -->
<div class="modal fade" id="subcategoryModal" tabindex="-1" aria-labelledby="subcategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
            <div class="modal-header border-bottom border-color">
                <h5 class="modal-title fw-bold" id="subcategoryModalLabel">Add Subcategory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-close-btn-filter);"></button>
            </div>
            <form id="subcategory-form" autocomplete="off">
                <input type="hidden" name="_method" id="sub-form-method" value="POST">
                <input type="hidden" name="sub_id" id="subcategory-id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Parent Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                        </select>
                        <div class="invalid-feedback" id="err-category_id"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sub_name" class="form-label">Subcategory Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="sub_name" class="form-control" required placeholder="e.g. Cables, Chargers, Protective Cases">
                        <div class="invalid-feedback" id="err-sub_name"></div>
                    </div>
                </div>
                <div class="modal-footer border-top border-color">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save-subcategory-btn">Save Subcategory</button>
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
    // 1. Initialize Tables
    var catTable = $('#categories-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('categories.index') }}"
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'subcategories_count'},
            {data: 'status', orderable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']]
    });

    var subTable = $('#subcategories-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('subcategories.list') }}"
        },
        columns: [
            {data: 'id'},
            {data: 'category'},
            {data: 'name'},
            {data: 'status', orderable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[0, 'desc']]
    });

    // 2. Fetch Active Categories for Subcategory Dropdown Select
    function loadCategoriesDropdown(selectedId = null) {
        $.ajax({
            type: 'GET',
            url: "{{ route('categories.index') }}",
            success: function(response) {
                var select = $('#category_id');
                select.empty().append('<option value="">Select Category</option>');
                
                // Get roles/data from ajax response (DataTable sends a 'data' array)
                $.each(response.data, function(idx, val) {
                    // Extract category details from data properties (like name and id)
                    // Note: table JSON sends HTML formatted name, let's pull list correctly.
                    // Or we can fetch categories directly, but reading response.data is easy
                    // Wait, let's query the database to get only active raw category objects.
                    // We can also query via an endpoint. Let's make it easy:
                    // Actually, we can fetch all categories from Category model via a simpler call or parse the DataTable data
                });
            }
        });
    }

    // Better: let's query a separate sub route or use direct fetch.
    // Let's implement active category query helper
    function populateCategoryOptions(targetSelectId, selectedValue = '') {
        $.ajax({
            url: "{{ route('categories.index') }}",
            type: 'GET',
            success: function(response) {
                // Since this index ajax call returns datatables structure,
                // we can call a custom query or parse response data.
                var select = $(targetSelectId);
                select.empty().append('<option value="">Select Category</option>');
                
                // We'll write a simple load from JSON:
                $.each(response.data, function(index, item) {
                    var isSelected = (item.id == selectedValue) ? 'selected' : '';
                    select.append('<option value="' + item.id + '" ' + isSelected + '>' + item.name + '</option>');
                });
            }
        });
    }

    // 3. Category Form Submit
    $('#add-category-btn').on('click', function() {
        $('#categoryModalLabel').text('Add Category');
        $('#form-method').val('POST');
        $('#category-form')[0].reset();
        $('#category-id').val('');
        $('.is-invalid').removeClass('is-invalid');
    });

    $('#category-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var id = $('#category-id').val();
        var url = id ? "{{ url('categories') }}/" + id : "{{ route('categories.store') }}";
        
        $.ajax({
            type: 'POST',
            url: url,
            data: $(this).serialize(),
            success: function(response) {
                hideLoader();
                $('#categoryModal').modal('hide');
                catTable.ajax.reload();
                subTable.ajax.reload(); // Reload subcategories to sync category names
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
                    toastr.error(xhr.responseJSON?.error || 'Failed to save category.');
                }
            }
        });
    });

    // Edit Category
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        showLoader();

        $.ajax({
            type: 'GET',
            url: "{{ url('categories') }}/" + id,
            success: function(response) {
                hideLoader();
                $('#categoryModalLabel').text('Edit Category: ' + response.name);
                $('#form-method').val('PUT');
                $('#category-id').val(response.id);
                $('#name').val(response.name);
                $('#status').prop('checked', response.status);
                $('.is-invalid').removeClass('is-invalid');
                $('#categoryModal').modal('show');
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Failed to retrieve category details.');
            }
        });
    });

    // Delete Category
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This category and its empty subcategories will be soft-deleted.",
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
                    url: "{{ url('categories') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        catTable.ajax.reload();
                        subTable.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error(xhr.responseJSON?.error || 'Failed to delete category.');
                    }
                });
            }
        });
    });

    // Toggle Category Status
    $(document).on('change', '.toggle-status', function() {
        var id = $(this).data('id');
        var checkbox = $(this);

        $.ajax({
            type: 'POST',
            url: "{{ url('categories') }}/" + id + "/toggle-status",
            success: function(response) {
                toastr.success(response.success);
                subTable.ajax.reload();
            },
            error: function(xhr) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error('Failed to update category status.');
            }
        });
    });

    // ==========================================
    // Subcategory Handlers
    // ==========================================

    $('#add-subcategory-btn').on('click', function() {
        $('#subcategoryModalLabel').text('Add Subcategory');
        $('#sub-form-method').val('POST');
        $('#subcategory-form')[0].reset();
        $('#subcategory-id').val('');
        $('.is-invalid').removeClass('is-invalid');
        populateCategoryOptions('#category_id');
        $('#subcategoryModal').modal('show');
    });

    $('#subcategory-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var id = $('#subcategory-id').val();
        var url = id ? "{{ url('subcategories') }}/" + id : "{{ route('subcategories.store') }}";
        var formData = $(this).serialize();

        // Include PUT method in serialize if editing
        if (id) {
            formData += '&_method=PUT';
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            success: function(response) {
                hideLoader();
                $('#subcategoryModal').modal('hide');
                subTable.ajax.reload();
                catTable.ajax.reload(); // Update counts
                toastr.success(response.success);
            },
            error: function(xhr) {
                hideLoader();
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        if (key === 'name') {
                            $('#sub_name').addClass('is-invalid');
                            $('#err-sub_name').text(val[0]);
                        } else {
                            $('#' + key).addClass('is-invalid');
                            $('#err-' + key).text(val[0]);
                        }
                    });
                } else {
                    toastr.error(xhr.responseJSON?.error || 'Failed to save subcategory.');
                }
            }
        });
    });

    // Edit Subcategory
    $(document).on('click', '.edit-subcategory-btn', function() {
        var id = $(this).data('id');
        showLoader();

        $.ajax({
            type: 'GET',
            url: "{{ url('subcategories') }}/" + id,
            success: function(response) {
                hideLoader();
                $('#subcategoryModalLabel').text('Edit Subcategory');
                $('#sub-form-method').val('PUT');
                $('#subcategory-id').val(response.id);
                $('#sub_name').val(response.name);
                populateCategoryOptions('#category_id', response.category_id);
                $('.is-invalid').removeClass('is-invalid');
                $('#subcategoryModal').modal('show');
            },
            error: function(xhr) {
                hideLoader();
                toastr.error('Failed to retrieve subcategory details.');
            }
        });
    });

    // Toggle Subcategory Status
    $(document).on('change', '.toggle-subcategory-status', function() {
        var id = $(this).data('id');
        var checkbox = $(this);

        $.ajax({
            type: 'POST',
            url: "{{ url('subcategories') }}/" + id + "/toggle-status",
            success: function(response) {
                toastr.success(response.success);
            },
            error: function(xhr) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error('Failed to update subcategory status.');
            }
        });
    });

    // Delete Subcategory
    $(document).on('click', '.delete-subcategory-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This subcategory will be soft-deleted.",
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
                    url: "{{ url('subcategories') }}/" + id,
                    success: function(response) {
                        hideLoader();
                        subTable.ajax.reload();
                        catTable.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        hideLoader();
                        toastr.error(xhr.responseJSON?.error || 'Failed to delete subcategory.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
