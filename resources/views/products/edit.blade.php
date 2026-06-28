@extends('layouts.app')

@section('title', 'Edit Product')
@section('module-title', 'Catalog')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<form id="product-edit-form" autocomplete="off">
    @csrf
    @method('PUT')
    
    <div class="row g-4">
        <!-- Top Segment: Core Info (Full Width) -->
        <div class="col-12">
            <div class="card shadow-sm border border-color">
                <div class="card-header bg-surface border-bottom border-color fw-bold">
                    <i class="bi bi-box me-2 text-primary"></i>Product General Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required value="{{ $product->name }}" placeholder="e.g. iPhone 15 Pro Max">
                            <div class="invalid-feedback" id="err-name"></div>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-center mt-3 mt-md-0">
                            <div class="form-check form-switch ps-5">
                                <input type="checkbox" name="is_imei_tracked" id="is_imei_tracked" class="form-check-input" value="1" {{ $product->is_imei_tracked ? 'checked' : '' }}>
                                <label for="is_imei_tracked" class="form-check-label fw-bold">Track by IMEI Number</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="brand_id" class="form-label fw-semibold">Brand <span class="text-danger">*</span></label>
                            <select name="brand_id" id="brand_id" class="form-select" required>
                                <option value="">Select Brand</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ $b->id == $product->brand_id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-brand_id"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="model_no" class="form-label fw-semibold">Model No</label>
                            <input type="text" name="model_no" id="model_no" class="form-control" value="{{ $product->model_no }}" placeholder="e.g. A3106">
                            <div class="invalid-feedback" id="err-model_no"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ $c->id == $product->category_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-category_id"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sub_category_id" class="form-label fw-semibold">Subcategory</label>
                            <select name="sub_category_id" id="sub_category_id" class="form-select">
                                <option value="">Select Subcategory</option>
                                @foreach($subCategories as $sc)
                                    <option value="{{ $sc->id }}" {{ $sc->id == $product->sub_category_id ? 'selected' : '' }}>{{ $sc->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="err-sub_category_id"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-0">
                                <label for="description" class="form-label fw-semibold">Description / Specifications</label>
                                <textarea name="description" id="description" class="form-control" rows="2" placeholder="Enter key product features...">{{ $product->description }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="image" class="form-label fw-semibold">Thumbnail (Primary Image)</label>
                                @if($product->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" class="rounded border" width="60" height="60" style="object-fit: cover;">
                                        <small class="text-muted d-block mt-1">Current Image</small>
                                    </div>
                                @endif
                                <input type="file" name="image" id="image" class="form-control form-control-sm">
                                <div class="invalid-feedback" id="err-image"></div>
                            </div>
                            <div class="mb-3">
                                <label for="gallery_images" class="form-label fw-semibold">Gallery Images (Multiple)</label>
                                <input type="file" name="gallery_images[]" id="gallery_images" class="form-control form-control-sm" multiple>
                                <div class="invalid-feedback" id="err-gallery_images"></div>
                            </div>
                            
                            @if($product->images->count() > 0)
                                <div class="mb-0">
                                    <label class="form-label fw-semibold d-block">Current Gallery Images</label>
                                    <div class="d-flex flex-wrap gap-2" id="gallery-container">
                                        @foreach($product->images as $img)
                                            <div class="position-relative gallery-item-wrapper" data-id="{{ $img->id }}" style="width: 60px; height: 60px;">
                                                <img src="{{ asset('storage/' . $img->image_path) }}" alt="Gallery Image" class="rounded border w-100 h-100" style="object-fit: cover;">
                                                <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 p-0 rounded-circle remove-gallery-btn" style="width: 18px; height: 18px; line-height: 18px; font-size: 10px; border: none;" data-id="{{ $img->id }}" title="Delete image">
                                                    &times;
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="delete-gallery-inputs"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Segment: Variants Builder (Full Width) -->
        <div class="col-12">
            <div class="card shadow-sm border border-color">
                <div class="card-header bg-surface border-bottom border-color d-flex justify-content-between align-items-center fw-bold">
                    <span><i class="bi bi-sliders me-2 text-primary"></i>Product Variants & Pricing</span>
                    <button type="button" class="btn btn-primary px-3 btn-sm" id="add-variant-row-btn">
                        <i class="bi bi-plus-lg me-1"></i> Add Option
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0" id="variants-table">
                            <thead>
                                <tr>
                                    <th>Color</th>
                                    <th>Storage</th>
                                    <th>RAM</th>
                                    <th style="min-width: 220px;">SKU *</th>
                                    <th style="min-width: 150px;">Cost Price ($) *</th>
                                    <th style="min-width: 150px;">Selling Price ($) *</th>
                                    <th style="min-width: 100px;">Alert Qty *</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="variants-tbody">
                                @foreach($product->variants as $idx => $variant)
                                <tr class="variant-row" data-index="{{ $idx }}">
                                    <input type="hidden" name="variants[{{ $idx }}][id]" value="{{ $variant->id }}">
                                    <td>
                                        <select name="variants[{{ $idx }}][color_id]" class="form-select var-color">
                                            <option value="">Color</option>
                                            @foreach($colors as $col)
                                                <option value="{{ $col->id }}" data-name="{{ $col->name }}" {{ $col->id == $variant->color_id ? 'selected' : '' }}>{{ $col->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="variants[{{ $idx }}][storage_variant_id]" class="form-select var-storage">
                                            <option value="">Storage</option>
                                            @foreach($storage as $st)
                                                <option value="{{ $st->id }}" data-name="{{ $st->value }}" {{ $st->id == $variant->storage_variant_id ? 'selected' : '' }}>{{ $st->value }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="variants[{{ $idx }}][ram_variant_id]" class="form-select var-ram">
                                            <option value="">RAM</option>
                                            @foreach($ram as $rm)
                                                <option value="{{ $rm->id }}" data-name="{{ $rm->value }}" {{ $rm->id == $variant->ram_variant_id ? 'selected' : '' }}>{{ $rm->value }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="variants[{{ $idx }}][sku]" class="form-control var-sku" required value="{{ $variant->sku }}" placeholder="SKU">
                                    </td>
                                    <td>
                                        <input type="number" name="variants[{{ $idx }}][cost_price]" class="form-control text-end" required min="0" step="0.01" value="{{ $variant->cost_price }}">
                                    </td>
                                    <td>
                                        <input type="number" name="variants[{{ $idx }}][selling_price]" class="form-control text-end" required min="0" step="0.01" value="{{ $variant->selling_price }}">
                                    </td>
                                    <td>
                                        <input type="number" name="variants[{{ $idx }}][alert_quantity]" class="form-control text-center" required min="0" value="{{ $variant->alert_quantity }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-link btn-xs text-danger remove-row-btn p-0"><i class="bi bi-trash fs-5"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 text-end bg-light border-top border-color d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="auto-sku-btn">
                            <i class="bi bi-magic me-1"></i> Auto-Gen SKUs
                        </button>
                        <div>
                            <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Save Catalog Product</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Master Option Templates (Hidden) -->
<div class="d-none">
    <select id="tmpl-colors">
        <option value="">Color</option>
        @foreach($colors as $col)
            <option value="{{ $col->id }}" data-name="{{ $col->name }}">{{ $col->name }}</option>
        @endforeach
    </select>
    <select id="tmpl-storage">
        <option value="">Storage</option>
        @foreach($storage as $st)
            <option value="{{ $st->id }}" data-name="{{ $st->value }}">{{ $st->value }}</option>
        @endforeach
    </select>
    <select id="tmpl-ram">
        <option value="">RAM</option>
        @foreach($ram as $rm)
            <option value="{{ $rm->id }}" data-name="{{ $rm->value }}">{{ $rm->value }}</option>
        @endforeach
    </select>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    var variantIndex = {{ $product->variants->count() }};

    // 1. Category-Subcategory loading
    $('#category_id').on('change', function() {
        var catId = $(this).val();
        var subSelect = $('#sub_category_id');
        subSelect.empty().append('<option value="">Select Subcategory</option>');
        
        if (catId) {
            $.ajax({
                url: "{{ url('categories') }}/" + catId + "/subcategories",
                type: "GET",
                success: function(res) {
                    $.each(res, function(idx, item) {
                        subSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                }
            });
        }
    });

    // 2. Add Row
    function addVariantRow() {
        var colorsOptions = $('#tmpl-colors').html();
        var storageOptions = $('#tmpl-storage').html();
        var ramOptions = $('#tmpl-ram').html();

        var row = `
        <tr class="variant-row" data-index="${variantIndex}">
            <input type="hidden" name="variants[${variantIndex}][id]" value="">
            <td>
                <select name="variants[${variantIndex}][color_id]" class="form-select var-color">
                    ${colorsOptions}
                </select>
            </td>
            <td>
                <select name="variants[${variantIndex}][storage_variant_id]" class="form-select var-storage">
                    ${storageOptions}
                </select>
            </td>
            <td>
                <select name="variants[${variantIndex}][ram_variant_id]" class="form-select var-ram">
                    ${ramOptions}
                </select>
            </td>
            <td>
                <input type="text" name="variants[${variantIndex}][sku]" class="form-control var-sku" required placeholder="SKU">
            </td>
            <td>
                <input type="number" name="variants[${variantIndex}][cost_price]" class="form-control text-end" required min="0" step="0.01" value="0.00">
            </td>
            <td>
                <input type="number" name="variants[${variantIndex}][selling_price]" class="form-control text-end" required min="0" step="0.01" value="0.00">
            </td>
            <td>
                <input type="number" name="variants[${variantIndex}][alert_quantity]" class="form-control text-center" required min="0" value="5">
            </td>
            <td>
                <button type="button" class="btn btn-link btn-xs text-danger remove-row-btn p-0"><i class="bi bi-trash fs-5"></i></button>
            </td>
        </tr>
        `;
        $('#variants-tbody').append(row);
        variantIndex++;
    }

    $('#add-variant-row-btn').on('click', function() {
        addVariantRow();
    });

    $(document).on('click', '.remove-row-btn', function() {
        if ($('.variant-row').length > 1) {
            $(this).closest('tr').remove();
        } else {
            toastr.warning('At least one product variant is required.');
        }
    });

    // 3. Auto SKU helper
    function generateSKU(row) {
        var prodName = $('#name').val().trim() || 'PROD';
        var color = row.find('.var-color option:selected').data('name') || '';
        var storage = row.find('.var-storage option:selected').data('name') || '';
        var ram = row.find('.var-ram option:selected').data('name') || '';
        
        var cleanName = prodName.replace(/[^a-zA-Z0-9]/g, '').substring(0, 8).toUpperCase();
        var skuParts = [cleanName];
        if (color) skuParts.push(color.replace(/[^a-zA-Z0-9]/g, '').toUpperCase());
        if (storage) skuParts.push(storage.replace(/[^a-zA-Z0-9]/g, '').toUpperCase());
        if (ram) skuParts.push(ram.replace(/[^a-zA-Z0-9]/g, '').toUpperCase());

        row.find('.var-sku').val(skuParts.join('-'));
    }

    $('#auto-sku-btn').on('click', function() {
        $('.variant-row').each(function() {
            generateSKU($(this));
        });
    });

    // Remove Gallery Image Handler
    $(document).on('click', '.remove-gallery-btn', function() {
        var imgId = $(this).data('id');
        $('#delete-gallery-inputs').append('<input type="hidden" name="delete_gallery_ids[]" value="' + imgId + '">');
        $(this).closest('.gallery-item-wrapper').remove();
    });

    // 4. Update Product AJAX submit
    $('#product-edit-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        showLoader();

        var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: "{{ route('products.update', $product->id) }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                hideLoader();
                toastr.success(res.success);
                setTimeout(function() {
                    window.location.href = "{{ route('products.index') }}";
                }, 1000);
            },
            error: function(xhr) {
                hideLoader();
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        var input = $('[name="' + key + '"]');
                        if (input.length) {
                            input.addClass('is-invalid');
                            $('#err-' + key).text(val[0]);
                        } else {
                            var keyParts = key.split('.');
                            if (keyParts[0] === 'variants') {
                                var idx = keyParts[1];
                                var field = keyParts[2];
                                var row = $('.variant-row[data-index="' + idx + '"]');
                                if (row.length) {
                                    row.find('[name="variants[' + idx + '][' + field + ']"]').addClass('is-invalid');
                                }
                            }
                        }
                    });
                    toastr.error('Please fix the validation errors.');
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
                }
            }
        });
    });
});
</script>
@endpush
