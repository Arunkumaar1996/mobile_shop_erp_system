@extends('layouts.frontend')

@section('title', 'Premium Mobile Store')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 animate-fade-in">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 uppercase fw-bold tracking-wider text-xs">New Collection Arrival</span>
                <h1 class="display-3 fw-bold mb-4 tracking-tight" style="color: var(--text-primary); line-height: 1.1;">
                    Next-Gen <span class="text-primary">Devices</span> are Here.
                </h1>
                <p class="lead text-muted mb-4">
                    Explore the latest flagship smartphones and premium smart accessories. Real-time warehouse availability check, immediate shipping options.
                </p>
                <div class="d-flex gap-3">
                    <a href="{{ route('shop') }}" class="btn btn-primary btn-lg px-4 py-3 fw-semibold">
                        <i class="bi bi-cart me-2"></i>Browse Catalog
                    </a>
                    <a href="#featured" class="btn btn-outline-secondary btn-lg px-4 py-3 fw-semibold">
                        Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 animate-scale-in text-center">
                <div class="position-relative d-inline-block">
                    <!-- Floating visual elements -->
                    <div class="bg-primary opacity-10 rounded-circle position-absolute start-50 top-50 translate-middle" style="width: 450px; height: 450px; filter: blur(50px);"></div>
                    <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=600&auto=format&fit=crop" alt="Premium Devices" class="img-fluid rounded-4 position-relative" style="max-height: 380px; box-shadow: 0 20px 40px rgba(0,0,0,0.12); object-fit: cover; width: 450px;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Brand Grid -->
<section class="py-5" style="border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="text-center mb-4">
            <p class="text-xs fw-bold text-muted uppercase tracking-widest mb-0">Authorized Flagship Manufacturers</p>
        </div>
        <div class="row row-cols-2 row-cols-md-4 g-4 justify-content-center align-items-center text-center opacity-75">
            @foreach($brands as $b)
                <div class="col">
                    <div class="p-3 border border-color rounded-3 bg-surface hover-shadow transition-all">
                        <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-award text-primary me-2"></i>{{ $b->name }}</h6>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Section -->
<section id="featured" class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <span class="text-xs fw-bold text-primary uppercase tracking-widest">Selected Showroom Highlights</span>
                <h2 class="fw-bold mb-0 mt-1">Latest Arrivals in Stock</h2>
            </div>
            <a href="{{ route('shop') }}" class="btn btn-outline-primary btn-sm">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse($featuredVariants as $index => $v)
                @php
                    $attributes = [];
                    if ($v->color) $attributes[] = $v->color->name;
                    if ($v->storageVariant) $attributes[] = $v->storageVariant->value;
                    if ($v->ramVariant) $attributes[] = $v->ramVariant->value;

                    $stockCount = $v->stocks->sum('quantity');
                @endphp
                <div class="col-md-6 col-lg-3">
                    <div class="product-card rounded-4 overflow-hidden position-relative h-100" style="animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) {{ $index * 0.1 }}s forwards;">
                        <!-- Product Image Backdrop -->
                        <div class="product-img-box position-relative overflow-hidden py-4 d-flex align-items-center justify-content-center" style="background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.08) 0%, rgba(255, 255, 255, 0) 70%); height: 180px;">
                            
                            @php
                                $galleryPaths = [];
                                if ($v->product->image) {
                                    $galleryPaths[] = $v->product->image;
                                }
                                foreach ($v->product->images as $img) {
                                    $galleryPaths[] = $img->image_path;
                                }
                            @endphp

                            @if(count($galleryPaths) > 1)
                                <div id="carousel-product-home-{{ $v->id }}" class="carousel slide carousel-fade w-100 h-100" data-bs-ride="carousel" data-bs-interval="3500">
                                    <div class="carousel-inner h-100">
                                        @foreach($galleryPaths as $gIndex => $gPath)
                                            <div class="carousel-item {{ $gIndex === 0 ? 'active' : '' }} h-100">
                                                <div class="d-flex align-items-center justify-content-center h-100 w-100">
                                                    <img src="{{ asset('storage/' . $gPath) }}" alt="{{ $v->product->name }}" class="icon-glow-wrapper rounded border-0 shadow-sm" style="max-height: 140px; max-width: 80%; object-fit: contain; transition: transform 0.3s ease;">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-product-home-{{ $v->id }}" data-bs-slide="prev" style="width: 12%;">
                                        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true" style="width: 22px; height: 22px; background-size: 50%;"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-product-home-{{ $v->id }}" data-bs-slide="next" style="width: 12%;">
                                        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true" style="width: 22px; height: 22px; background-size: 50%;"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            @elseif(count($galleryPaths) === 1)
                                <img src="{{ asset('storage/' . $galleryPaths[0]) }}" alt="{{ $v->product->name }}" class="icon-glow-wrapper rounded border-0 shadow-sm" style="max-height: 140px; max-width: 90%; object-fit: contain; transition: transform 0.3s ease;">
                            @elseif($v->product->category->name === 'Accessories')
                                <div class="icon-glow-wrapper bg-info-subtle rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; transition: transform 0.3s ease;">
                                    <i class="bi bi-usb-plug text-info fs-2"></i>
                                </div>
                            @else
                                <div class="icon-glow-wrapper bg-primary-subtle rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; transition: transform 0.3s ease;">
                                    <i class="bi bi-phone text-primary fs-2"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Card Contents -->
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-3xs text-muted uppercase tracking-wider fw-bold">{{ $v->product->category->name }}</span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.65rem; font-weight: 600;">{{ $v->product->brand->name }}</span>
                                </div>
                                @if($stockCount > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.7rem;">In Stock</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.7rem;">Out of Stock</span>
                                @endif
                            </div>
                            
                            <h5 class="fw-bold mb-1 text-truncate text-primary-hover" title="{{ $v->product->name }}">{{ $v->product->name }}</h5>
                            <p class="text-3xs text-muted mb-3">{{ $v->sku }}</p>
                            
                            <!-- Specifications Metas -->
                            <div class="d-flex flex-wrap gap-1.5 mb-3">
                                @if($v->color)
                                    <span class="badge bg-light text-dark border border-color d-flex align-items-center gap-1 py-1.5 px-2" style="font-size: 0.7rem; font-weight: 500;">
                                        <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: #{{ $v->color->code }}; border: 1px solid #aaa;"></span>
                                        {{ $v->color->name }}
                                    </span>
                                @endif
                                @if($v->storageVariant)
                                    <span class="badge bg-light text-dark border border-color d-flex align-items-center gap-1 py-1.5 px-2" style="font-size: 0.7rem; font-weight: 500;">
                                        <i class="bi bi-device-hdd text-muted"></i>
                                        {{ $v->storageVariant->value }}
                                    </span>
                                @endif
                                @if($v->ramVariant)
                                    <span class="badge bg-light text-dark border border-color d-flex align-items-center gap-1 py-1.5 px-2" style="font-size: 0.7rem; font-weight: 500;">
                                        <i class="bi bi-cpu text-muted"></i>
                                        {{ $v->ramVariant->value }}
                                    </span>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-color mt-3">
                                <div>
                                    <span class="text-3xs text-muted d-block uppercase tracking-wider fw-semibold">Retail Price</span>
                                    <span class="fw-extrabold fs-4 text-primary">${{ number_format($v->selling_price, 2) }}</span>
                                </div>
                                <a href="https://wa.me/15550100?text=Hello,%20I%27m%20interested%20in%20the%20{{ urlencode($v->product->name) }}%20{{ urlencode($v->sku) }}" target="_blank" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-bold">
                                    <i class="bi bi-chat-dots me-1"></i> Inquire
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-box-seam text-muted display-3"></i>
                    <p class="text-muted mt-3">No products available at the moment. Run database seeders to populate.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Banner promo -->
<section class="py-5 bg-surface border-top border-bottom border-color">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-8 text-center text-md-start">
                <h3 class="fw-bold mb-2">Want to manage inventory levels?</h3>
                <p class="text-muted mb-0">Log in with corporate staff credentials to view purchase logs, receipts, warehouses, and sales transactions.</p>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <a href="{{ route('login') }}" class="btn btn-primary px-4 py-3 fw-semibold">
                    Go to Corporate ERP Portal <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
