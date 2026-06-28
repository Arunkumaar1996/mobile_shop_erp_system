<div class="row g-4">
    @forelse($variants as $index => $v)
        @php
            $attributes = [];
            if ($v->color) $attributes[] = $v->color->name;
            if ($v->storageVariant) $attributes[] = $v->storageVariant->value;
            if ($v->ramVariant) $attributes[] = $v->ramVariant->value;

            $stockCount = $v->stocks->sum('quantity');
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="product-card rounded-4 overflow-hidden position-relative h-100" style="animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) {{ ($index % 3) * 0.1 }}s forwards;">
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
                        <div id="carousel-product-{{ $v->id }}" class="carousel slide carousel-fade w-100 h-100" data-bs-ride="carousel" data-bs-interval="3500">
                            <div class="carousel-inner h-100">
                                @foreach($galleryPaths as $gIndex => $gPath)
                                    <div class="carousel-item {{ $gIndex === 0 ? 'active' : '' }} h-100">
                                        <div class="d-flex align-items-center justify-content-center h-100 w-100">
                                            <img src="{{ asset('storage/' . $gPath) }}" alt="{{ $v->product->name }}" class="icon-glow-wrapper rounded border-0 shadow-sm" style="max-height: 140px; max-width: 80%; object-fit: contain; transition: transform 0.3s ease;">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-product-{{ $v->id }}" data-bs-slide="prev" style="width: 12%;">
                                <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true" style="width: 22px; height: 22px; background-size: 50%;"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-product-{{ $v->id }}" data-bs-slide="next" style="width: 12%;">
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
            <i class="bi bi-search text-muted display-3"></i>
            <p class="text-muted mt-3">No products match your selected specification filters.</p>
        </div>
    @endforelse
</div>

@if($variants->hasPages())
    <div class="d-flex justify-content-center mt-5" id="pagination-links">
        {!! $variants->withQueryString()->links('pagination::bootstrap-5') !!}
    </div>
@endif
