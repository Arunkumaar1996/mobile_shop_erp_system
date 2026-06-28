<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') | Mobile Shop</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- jQuery (Loaded early in head to prevent race conditions with type="module" deferred scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Vite CSS -->
    @vite(['resources/css/app.css'])

    <!-- Extra CSS Styles for Public Pages -->
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            transition: background-color 0.3s, color 0.3s;
        }
        
        .navbar-frontend {
            background-color: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            transition: background-color 0.3s;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--text-primary) !important;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-secondary) !important;
            transition: color 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }

        .hero-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
            border-bottom: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleUp {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-scale-in {
            animation: scaleUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .product-card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s;
            opacity: 0; /* Fade in animation target */
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
            border-color: var(--primary-color);
        }

        .product-img-box {
            height: 220px;
            background-color: rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-badge {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        footer {
            background-color: var(--bg-surface);
            border-top: 1px solid var(--border-color);
            padding: 4rem 0 2rem;
            margin-top: 5rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Theme variables switcher container -->
    <div id="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-frontend">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-phone-vibrate text-primary me-2"></i>MOBILE<span class="text-primary">SHOP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#frontendNav" aria-controls="frontendNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="frontendNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('shop') ? 'active' : '' }}" href="{{ route('shop') }}">Shop Products</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Font Size Selector Button Group -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="Font Size Changer">
                        <button type="button" class="btn btn-outline-secondary font-size-btn" data-size="small" title="Decrease Font Size">A-</button>
                        <button type="button" class="btn btn-outline-secondary font-size-btn active" data-size="normal" title="Normal Font Size">A</button>
                        <button type="button" class="btn btn-outline-secondary font-size-btn" data-size="large" title="Increase Font Size">A+</button>
                    </div>

                    <!-- Light / Dark Switch -->
                    <button class="btn btn-outline-secondary btn-sm" id="theme-toggle" title="Toggle Light/Dark Theme">
                        <i class="bi bi-moon" id="theme-icon"></i>
                    </button>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm px-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Staff Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-phone-vibrate text-primary me-2"></i>MOBILE<span class="text-primary">SHOP</span></h5>
                    <p class="text-muted">Premium retailer of high-performance devices, tablets, and smart accessories with real-time digital warehouse stock levels.</p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h6 class="fw-bold mb-3">Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('home') }}" class="text-muted text-decoration-none hover-primary">Home</a></li>
                        <li class="mb-2"><a href="{{ route('shop') }}" class="text-muted text-decoration-none hover-primary">Shop Catalog</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-4">
                    <h6 class="fw-bold mb-3">Address & Store Details</h6>
                    <p class="text-muted mb-1"><i class="bi bi-geo-alt me-2"></i>456 Main Avenue, Retail City</p>
                    <p class="text-muted"><i class="bi bi-telephone me-2"></i>+1 555-0100</p>
                </div>
            </div>
            <hr class="border-color mb-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted mb-0">&copy; {{ date('y') }} Mobile Shop ERP. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Vite JS -->
    @vite(['resources/js/app.js'])

    <!-- Custom Theme & Font Size JS Switch helper -->
    <script>
        // Inline setup to prevent theme flashing
        var savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        document.documentElement.setAttribute('data-theme', savedTheme);

        var savedFontSize = localStorage.getItem('font-size') || 'normal';
        document.documentElement.classList.add('font-size-' + savedFontSize);

        document.addEventListener('DOMContentLoaded', function() {
            // Theme Switcher
            var themeToggleBtn = document.getElementById('theme-toggle');
            var themeIcon = document.getElementById('theme-icon');

            function updateThemeUI(theme) {
                if (theme === 'dark') {
                    themeIcon.className = 'bi bi-sun';
                } else {
                    themeIcon.className = 'bi bi-moon';
                }
            }

            updateThemeUI(savedTheme);

            themeToggleBtn.addEventListener('click', function() {
                var currentTheme = document.documentElement.getAttribute('data-bs-theme');
                var newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeUI(newTheme);
            });

            // Font Size Switcher
            var fontSizeButtons = document.querySelectorAll('.font-size-btn');
            
            function updateFontSizeUI(size) {
                fontSizeButtons.forEach(function(btn) {
                    if (btn.getAttribute('data-size') === size) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            updateFontSizeUI(savedFontSize);

            fontSizeButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var size = this.getAttribute('data-size');
                    
                    // Remove old classes
                    document.documentElement.classList.remove('font-size-small', 'font-size-normal', 'font-size-large');
                    // Add new class
                    document.documentElement.classList.add('font-size-' + size);
                    
                    localStorage.setItem('font-size', size);
                    updateFontSizeUI(size);
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
