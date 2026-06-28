import './bootstrap';
import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import toastr from 'toastr';

// Expose libraries to the window object for inline Scripts
window.bootstrap = bootstrap;
window.Swal = Swal;
window.toastr = toastr;

// Configure Toastr Options
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};

// Document Ready Operations
$(function() {
    // 1. Setup AJAX CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 2. Sidebar Toggle Handler
    $(document).on('click', '#menu-toggle', function(e) {
        e.preventDefault();
        
        if (window.innerWidth < 992) {
            $('#wrapper').toggleClass('sidebar-open');
            
            // Handle mobile backdrop
            if ($('#wrapper').hasClass('sidebar-open')) {
                if (!$('.sidebar-backdrop').length) {
                    $('body').append('<div class="sidebar-backdrop"></div>');
                    setTimeout(function() {
                        $('.sidebar-backdrop').addClass('active');
                    }, 50);
                }
            } else {
                removeSidebarBackdrop();
            }
        } else {
            $('#wrapper').toggleClass('toggled');
        }
    });

    $(document).on('click', '#sidebar-close-btn', function(e) {
        e.preventDefault();
        if (window.innerWidth < 992) {
            $('#wrapper').removeClass('sidebar-open');
        } else {
            $('#wrapper').addClass('toggled');
        }
        removeSidebarBackdrop();
    });

    $(document).on('click', '.sidebar-backdrop', function() {
        if (window.innerWidth < 992) {
            $('#wrapper').removeClass('sidebar-open');
        }
        removeSidebarBackdrop();
    });

    function removeSidebarBackdrop() {
        $('.sidebar-backdrop').removeClass('active');
        setTimeout(function() {
            $('.sidebar-backdrop').remove();
        }, 300);
    }

    $(window).on('resize', function() {
        if (window.innerWidth >= 992) {
            removeSidebarBackdrop();
            $('#wrapper').removeClass('sidebar-open');
        }
    });

    // 3. Dark/Light Theme Handler
    // Theme Swapper
    const savedTheme = localStorage.getItem('theme') || 'light';
    $('html').attr('data-theme', savedTheme).attr('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);

    $(document).on('click', '#theme-toggle', function(e) {
        e.preventDefault();
        const currentTheme = $('html').attr('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        $('html').attr('data-theme', newTheme).attr('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        const icon = $('#theme-toggle i');
        if (theme === 'dark') {
            icon.removeClass('bi-moon-fill').addClass('bi-sun-fill');
        } else {
            icon.removeClass('bi-sun-fill').addClass('bi-moon-fill');
        }
    }

    // Font Size Accessibility Swapper
    const savedFontSize = localStorage.getItem('font-size') || 'normal';
    $('html').addClass('font-size-' + savedFontSize);
    updateFontSizeUI(savedFontSize);

    $(document).on('click', '.font-size-btn', function(e) {
        e.preventDefault();
        const size = $(this).data('size');
        
        $('html').removeClass('font-size-small font-size-normal font-size-large')
                 .addClass('font-size-' + size);
        localStorage.setItem('font-size', size);
        updateFontSizeUI(size);
    });

    function updateFontSizeUI(size) {
        $('.font-size-btn').removeClass('active');
        $(`.font-size-btn[data-size="${size}"]`).addClass('active');
    }
});

// 4. Global Loading Overlay Functions
window.showLoader = function() {
    $('#loading-overlay').addClass('active');
}

window.hideLoader = function() {
    $('#loading-overlay').removeClass('active');
}
