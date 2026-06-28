@extends('layouts.frontend')

@section('title', 'Shop Catalog')

@section('content')
<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Filters (Responsive Offcanvas) -->
        <div class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start bg-surface border-0" tabindex="-1" id="filterSidebar" aria-labelledby="filterSidebarLabel" style="max-width: 320px;">
                <div class="offcanvas-header border-bottom border-color bg-surface d-lg-none py-3 px-4">
                    <h5 class="offcanvas-title fw-bold" id="filterSidebarLabel"><i class="bi bi-funnel me-2 text-primary"></i>Filter Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filterSidebar" aria-label="Close"></button>
                </div>
                
                <div class="offcanvas-body p-0">
                    <div class="card shadow-sm p-4 w-100" style="background-color: var(--bg-surface); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 d-none d-lg-block"><i class="bi bi-filter-right me-1"></i>Filters</h5>
                            <button type="button" id="clear-all-filters" class="btn btn-link btn-xs text-decoration-none p-0">Clear All</button>
                        </div>
                        
                        <form id="filter-form">
                            <!-- Search -->
                            <div class="mb-4">
                                <label class="form-label text-xs fw-bold text-muted uppercase tracking-wider">Search Device</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 border-color"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" id="search-input" class="form-control form-control-sm border-start-0 border-color" placeholder="e.g. iPhone, S24...">
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="mb-4">
                                <label class="form-label text-xs fw-bold text-muted uppercase tracking-wider mb-2">Category</label>
                                @foreach($filterCategories as $cat)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input filter-checkbox" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}">
                                        <label class="form-check-label text-sm" for="cat_{{ $cat->id }}">
                                            {{ $cat->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Brands -->
                            <div class="mb-4">
                                <label class="form-label text-xs fw-bold text-muted uppercase tracking-wider mb-2">Brand</label>
                                @foreach($filterBrands as $br)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input filter-checkbox" type="checkbox" name="brands[]" value="{{ $br->id }}" id="br_{{ $br->id }}">
                                        <label class="form-check-label text-sm" for="br_{{ $br->id }}">
                                            {{ $br->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Specifications: Storage -->
                            <div class="mb-4">
                                <label class="form-label text-xs fw-bold text-muted uppercase tracking-wider mb-2">Storage Capacity</label>
                                @foreach($filterStorage as $st)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input filter-checkbox" type="checkbox" name="storage[]" value="{{ $st->id }}" id="st_{{ $st->id }}">
                                        <label class="form-check-label text-sm" for="st_{{ $st->id }}">
                                            {{ $st->value }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Specifications: RAM -->
                            <div class="mb-4">
                                <label class="form-label text-xs fw-bold text-muted uppercase tracking-wider mb-2">RAM Size</label>
                                @foreach($filterRam as $rm)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input filter-checkbox" type="checkbox" name="ram[]" value="{{ $rm->id }}" id="rm_{{ $rm->id }}">
                                        <label class="form-check-label text-sm" for="rm_{{ $rm->id }}">
                                            {{ $rm->value }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Specifications: Color -->
                            <div class="mb-4">
                                <label class="form-label text-xs fw-bold text-muted uppercase tracking-wider mb-2">Color Swatch</label>
                                @foreach($filterColors as $col)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input filter-checkbox" type="checkbox" name="colors[]" value="{{ $col->id }}" id="col_{{ $col->id }}">
                                        <label class="form-check-label text-sm" for="col_{{ $col->id }}">
                                            {{ $col->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Price range -->
                            <div class="mb-4">
                                <label class="form-label text-xs fw-bold text-muted uppercase tracking-wider mb-2">Price Limit ($)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" id="min-price" class="form-control form-control-sm border-color" placeholder="Min" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" id="max-price" class="form-control form-control-sm border-color" placeholder="Max" min="0">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Product Grid Section -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Products Catalog</h4>
                    <p class="text-xs text-muted mb-0">Browse device variations & available quantities.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- Filter Trigger Button on Mobile -->
                    <button class="btn btn-outline-primary btn-sm d-lg-none px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterSidebar" aria-controls="filterSidebar">
                        <i class="bi bi-funnel me-1"></i> Filter Options
                    </button>
                    
                    <label for="sort-select" class="text-xs text-muted text-nowrap">Sort By</label>
                    <select id="sort-select" class="form-select form-select-sm border-color" style="width: 150px;">
                        <option value="latest">Latest Arrivals</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid Wrapper -->
            <div id="product-grid-container">
                @include('frontend.partials.product_grid')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    var debounceTimer;

    // AJAX request helper to fetch filtered products
    function fetchFilteredProducts(page = 1, scrollToTop = false) {
        var container = $('#product-grid-container');
        container.css('opacity', '0.4');
        
        var formData = $('#filter-form').serialize();
        formData += '&page=' + page;
        formData += '&sort=' + $('#sort-select').val();

        $.ajax({
            url: "{{ route('shop') }}",
            type: "GET",
            data: formData,
            success: function(htmlResponse) {
                container.css('opacity', '1');
                container.html(htmlResponse);
                
                if (scrollToTop) {
                    $('html, body').animate({
                        scrollTop: container.offset().top - 120
                    }, 200);
                }
            },
            error: function() {
                container.css('opacity', '1');
                toastr.error('Failed to retrieve filtered catalog products.');
            }
        });
    }

    // Bind filters check triggers
    $('.filter-checkbox, #sort-select').on('change', function() {
        fetchFilteredProducts(1, false);
    });

    $('#min-price, #max-price').on('keyup change', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            fetchFilteredProducts(1, false);
        }, 500);
    });

    // Debounce search field
    $('#search-input').on('keyup input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            fetchFilteredProducts(1, false);
        }, 400);
    });

    // Handle AJAX pagination links click
    $(document).on('click', '#pagination-links a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var page = new URL(url).searchParams.get('page');
        if (page) {
            fetchFilteredProducts(page, true);
        }
    });

    // Clear filters
    $('#clear-all-filters').on('click', function() {
        $('#filter-form')[0].reset();
        fetchFilteredProducts(1, true);
    });
});
</script>
@endpush
