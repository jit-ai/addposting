// Main JavaScript file

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips (if needed)
    initTooltips();
    
    // Initialize carousel (if needed)
    initCarousel();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize search functionality
    initSearch();
    
    // Initialize file upload preview
    initFileUploadPreview();
    
    // Initialize mobile menu
    initMobileMenu();
    
    // Initialize other features
    initScrollAnimation();
    initDeleteConfirm();
    initLazyLoading();
});

// Initialize tooltips
function initTooltips() {
    // Add tooltip functionality if needed
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            // Create tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.position = 'absolute';
            tooltip.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            tooltip.style.color = 'white';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.zIndex = '1000';
            
            // Position tooltip
            const rect = this.getBoundingClientRect();
            tooltip.style.top = rect.bottom + 10 + 'px';
            tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
            
            // Add to body
            document.body.appendChild(tooltip);
            
            // Remove on mouse leave
            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            });
        });
    });
}

// Initialize carousel
function initCarousel() {
    // Add carousel functionality if needed
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
        const slides = carousel.querySelectorAll('.carousel-slide');
        const indicators = carousel.querySelectorAll('.carousel-indicator');
        let currentSlide = 0;
        
        // Show slide
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === index);
            });
        }
        
        // Next slide
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        // Auto slide
        setInterval(nextSlide, 5000);
        
        // Indicator click
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', function() {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });
    });
}

// Initialize form validation
function initFormValidation() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('invalid');
                showError(this, 'Please enter a valid email address');
            } else {
                this.classList.remove('invalid');
                removeError(this);
            }
        });
    });
    
    // Password validation
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const password = this.value;
            
            if (password && password.length < 6) {
                this.classList.add('invalid');
                showError(this, 'Password must be at least 6 characters');
            } else {
                this.classList.remove('invalid');
                removeError(this);
            }
        });
    });
    
    // Confirm password validation
    const confirmPasswordInputs = document.querySelectorAll('input[name="confirm_password"]');
    confirmPasswordInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = this.value;
            
            if (password && confirmPassword && password !== confirmPassword) {
                this.classList.add('invalid');
                showError(this, 'Passwords do not match');
            } else {
                this.classList.remove('invalid');
                removeError(this);
            }
        });
    });
    
    // Price validation
    const priceInputs = document.querySelectorAll('input[type="number"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const price = parseFloat(this.value);
            
            if (price <= 0) {
                this.classList.add('invalid');
                showError(this, 'Price must be greater than 0');
            } else {
                this.classList.remove('invalid');
                removeError(this);
            }
        });
    });
}

// Show error message
function showError(element, message) {
    // Remove existing error
    removeError(element);
    
    // Create error element
    const error = document.createElement('div');
    error.className = 'error-message';
    error.textContent = message;
    error.style.color = '#dc3545';
    error.style.fontSize = '0.875rem';
    error.style.marginTop = '0.25rem';
    
    // Add error after input
    element.parentNode.appendChild(error);
}

// Remove error message
function removeError(element) {
    const error = element.parentNode.querySelector('.error-message');
    if (error) {
        error.remove();
    }
}

// Initialize search functionality
function initSearch() {
    const searchForm = document.querySelector('.search-box form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            const categorySelect = this.querySelector('select[name="category"]');
            
            // Validate search input
            if (!searchInput.value.trim() && !categorySelect.value) {
                e.preventDefault();
                showError(searchInput, 'Please enter a search term or select a category');
                searchInput.classList.add('invalid');
            }
        });
    }
}

// Initialize file upload preview
function initFileUploadPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const files = this.files;
            const previewContainer = this.parentNode.querySelector('.file-preview');
            
            // Clear existing preview
            if (previewContainer) {
                previewContainer.innerHTML = '';
            }
            
            // Create preview container if it doesn't exist
            if (!previewContainer) {
                const preview = document.createElement('div');
                preview.className = 'file-preview';
                preview.style.display = 'flex';
                preview.style.gap = '10px';
                preview.style.marginTop = '10px';
                preview.style.flexWrap = 'wrap';
                this.parentNode.appendChild(preview);
            }
            
            // Preview images
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '5px';
                        img.style.border = '1px solid #e2e8f0';
                        img.style.padding = '5px';
                        img.style.backgroundColor = 'white';
                        img.style.cursor = 'pointer';
                        
                        // Remove image on click
                        img.addEventListener('click', function() {
                            img.remove();
                        });
                        
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    });
}

// Smooth scrolling for anchor links
function smoothScroll() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Mobile hamburger menu toggle
function initMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const mobileNav = document.querySelector('.mobile-nav');
    const overlay = document.querySelector('.mobile-nav-overlay') || createOverlay();
    
    if (hamburger) {
        hamburger.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMobileMenu();
        });
    }
    
    // Mobile nav close button
    const mobileNavClose = document.querySelector('.mobile-nav-close');
    if (mobileNavClose) {
        mobileNavClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeMobileMenu();
        });
    }
    
    // Close on overlay click
    overlay.addEventListener('click', closeMobileMenu);
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });
    
    // Mobile dropdown toggle
    const mobileDropdowns = document.querySelectorAll('.mobile-nav .dropdown-toggle');
    mobileDropdowns.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = this.parentElement;
            dropdown.classList.toggle('active');
        });
    });
    
    // Close on nav link click
    document.addEventListener('click', function(e) {
        if (e.target.closest('.mobile-nav a') && !e.target.closest('.dropdown-toggle')) {
            closeMobileMenu();
        }
    });
}

function toggleMobileMenu() {
    const mobileNav = document.querySelector('.mobile-nav');
    const overlay = document.querySelector('.mobile-nav-overlay');
    const hamburger = document.querySelector('.hamburger');
    
    if (!mobileNav || !overlay || !hamburger) return;
    
    mobileNav.classList.toggle('active');
    overlay.classList.toggle('active');
    document.body.classList.toggle('no-scroll');
    hamburger.setAttribute('aria-expanded', mobileNav.classList.contains('active'));
}

function closeMobileMenu() {
    const mobileNav = document.querySelector('.mobile-nav');
    const overlay = document.querySelector('.mobile-nav-overlay');
    const hamburger = document.querySelector('.hamburger');
    
    if (!mobileNav || !overlay || !hamburger) return;
    
    mobileNav.classList.remove('active');
    overlay.classList.remove('active');
    document.body.classList.remove('no-scroll');
    hamburger.setAttribute('aria-expanded', 'false');
}

function createOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'mobile-nav-overlay';
    overlay.setAttribute('aria-hidden', 'true');
    document.body.appendChild(overlay);
    return overlay;
}

// Prevent body scroll when menu open
const style = document.createElement('style');
style.textContent = `
    body.no-scroll {
        overflow: hidden;
        position: fixed;
        width: 100%;
    }
`;
document.head.appendChild(style);

// Add animation on scroll
function initScrollAnimation() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });
}

// Confirm dialog for delete actions
function initDeleteConfirm() {
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
}

// Initialize lazy loading for images
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px'
    });
    
    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });
}

// Add loading indicator
function showLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading';
    loading.style.position = 'fixed';
    loading.style.top = '0';
    loading.style.left = '0';
    loading.style.width = '100%';
    loading.style.height = '100%';
    loading.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    loading.style.display = 'flex';
    loading.style.alignItems = 'center';
    loading.style.justifyContent = 'center';
    loading.style.zIndex = '10000';
    
    const spinner = document.createElement('div');
    spinner.style.width = '50px';
    spinner.style.height = '50px';
    spinner.style.border = '3px solid #f3f3f3';
    spinner.style.borderTop = '3px solid #3498db';
    spinner.style.borderRadius = '50%';
    spinner.style.animation = 'spin 1s linear infinite';
    
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
    document.head.appendChild(style);
    
    loading.appendChild(spinner);
    document.body.appendChild(loading);
    
    return loading;
}

function hideLoading(loading) {
    if (loading && loading.parentNode) {
        loading.parentNode.removeChild(loading);
    }
}

// Handle form submission with loading
function initFormSubmission() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Show loading indicator
            const loading = showLoading();
            
            // Hide loading after 2 seconds (for demo purposes)
            setTimeout(() => {
                hideLoading(loading);
            }, 2000);
        });
    });
}