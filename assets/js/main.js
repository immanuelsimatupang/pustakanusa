/**
 * Kahfi Education Main JavaScript
 * Mengatur semua interaksi utama situs web
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi komponen Bootstrap
    initBootstrapComponents();
    
    // Update prayer time, date and time displays
    updateTimeDisplays();
    
    // Smooth scroll for navigation
    initSmoothScroll();
    
    // Toggle dark/light mode if needed
    initThemeToggle();
    
    // Add parallax effect to hero section if exists
    initParallaxEffect();
    
    // Initialize database status buttons if exists
    initDBStatusButtons();

    // Fix mobile navbar toggle
    initMobileNav();
    
    // Initialize back to top button
    initBackToTop();
    
    // Active navbar item based on current page
    setActiveNavItem();
    
    // Update time every minute
    setInterval(function() {
        updateHijriDate();
        updateNextPrayer();
    }, 60000);
    
    // Initialize dropdown hover on desktop
    initDropdownHover();
    
    // Handle specific page functionality
    initPageSpecificFunctions();

    // New components
    initTeacherGallery();
    initTestimonialCarousel();
});

/**
 * Inisialisasi komponen Bootstrap
 */
function initBootstrapComponents() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Bootstrap 5 already handles dropdowns with data-bs attributes
    // This is a fallback for custom dropdowns if needed
    var customDropdowns = document.querySelectorAll('.custom-dropdown-toggle');
    customDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('custom-dropdown-menu')) {
                dropdownMenu.classList.toggle('show');
            }
        });
    });

    // Close custom dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.custom-dropdown-toggle')) {
            var openDropdowns = document.querySelectorAll('.custom-dropdown-menu.show');
            openDropdowns.forEach(function(menu) {
                menu.classList.remove('show');
            });
        }
    });

    // Ensure all Bootstrap dropdowns are properly initialized
    const dropdownElements = document.querySelectorAll('.dropdown-toggle');
    dropdownElements.forEach(function(element) {
        new bootstrap.Dropdown(element);
    });

    // Fix dropdown menus that may go off-screen
    const dropdownMenus = document.querySelectorAll('.dropdown-menu');
    dropdownMenus.forEach(function(menu) {
        const parentDropdown = menu.closest('.dropdown');
        if (parentDropdown) {
            const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
            if (dropdownToggle) {
                dropdownToggle.addEventListener('shown.bs.dropdown', function() {
                    const menuRect = menu.getBoundingClientRect();
                    const rightEdge = menuRect.left + menuRect.width;
                    if (rightEdge > window.innerWidth) {
                        menu.style.left = 'auto';
                        menu.style.right = '0';
                    }
                });
            }
        }
    });
}

/**
 * Initialize dropdown hover behavior for desktop
 */
function initDropdownHover() {
    // Create dropdown instances for bootstrap if not already created
    const dropdownToggleElements = document.querySelectorAll('.dropdown-toggle');
    const dropdownInstances = {};
    
    dropdownToggleElements.forEach(toggle => {
        dropdownInstances[toggle.id || toggle.getAttribute('aria-labelledby')] = new bootstrap.Dropdown(toggle);
    });
    
    function handleHover() {
        if (window.innerWidth >= 992) { // Only for desktop
            const dropdowns = document.querySelectorAll('.navbar .dropdown');
            
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                if (!toggle || !menu) return;
                
                let timeout;
                
                dropdown.addEventListener('mouseenter', function() {
                    clearTimeout(timeout);
                    dropdowns.forEach(d => {
                        if (d !== dropdown) {
                            const t = d.querySelector('.dropdown-toggle');
                            if (t && dropdownInstances[t.id]) {
                                dropdownInstances[t.id].hide();
                            }
                        }
                    });
                    
                    if (toggle && dropdownInstances[toggle.id]) {
                        dropdownInstances[toggle.id].show();
                    }
                });
                
                dropdown.addEventListener('mouseleave', function() {
                    timeout = setTimeout(() => {
                        if (toggle && dropdownInstances[toggle.id]) {
                            dropdownInstances[toggle.id].hide();
                        }
                    }, 200);
                });
            });
        }
    }
    
    // Initial setup
    handleHover();
    
    // Re-setup on window resize
    window.addEventListener('resize', handleHover);
}

/**
 * Mobile navigation menu toggle behavior
 */
function initMobileNav() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (!navbarToggler || !navbarCollapse) return;
    
    // Create collapse instance
    const navbarCollapseInstance = new bootstrap.Collapse(navbarCollapse, {
        toggle: false
    });
    
    // Handle mobile dropdown behavior
    const dropdownToggles = document.querySelectorAll('.navbar-nav .dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth < 992) { // Only for mobile
                e.preventDefault();
                e.stopPropagation();
                
                const dropdownMenu = this.nextElementSibling;
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    // Close all other dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== this) {
                            const otherMenu = otherToggle.nextElementSibling;
                            if (otherMenu && otherMenu.classList.contains('show')) {
                                otherMenu.classList.remove('show');
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                    
                    // Toggle this dropdown
                    dropdownMenu.classList.toggle('show');
                    this.setAttribute('aria-expanded', 
                        dropdownMenu.classList.contains('show') ? 'true' : 'false');
                }
            }
        });
    });
    
    // Close dropdown menu when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            const isInsideNavbar = navbarCollapse.contains(e.target) || navbarToggler.contains(e.target);
            const isInsideDropdown = e.target.classList.contains('dropdown-toggle') || 
                                    e.target.closest('.dropdown-toggle');
            
            if (!isInsideDropdown && !isInsideNavbar && navbarCollapse.classList.contains('show')) {
                navbarCollapseInstance.hide();
            }
        }
    });
    
    // Close mobile menu when clicking a nav link that isn't a dropdown
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992 && navbarCollapse.classList.contains('show')) {
                navbarCollapseInstance.hide();
            }
        });
    });
}

/**
 * Update all time and date displays
 */
function updateTimeDisplays() {
    // Update current time
    const updateTime = function() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
        
        const currentTimeElements = document.querySelectorAll('.current-time');
        currentTimeElements.forEach(function(el) {
            el.textContent = timeString;
        });
    };
    
    // Initial update
    updateTime();
    
    // Update every second
    setInterval(updateTime, 1000);
    
    // Update Hijri date
    updateHijriDate();
    
    // Update next prayer time
    updateNextPrayer();
    
    // Update prayer countdown if on prayer times page
    if (document.getElementById('countdown')) {
        updatePrayerCountdown();
    }
}

/**
 * Update Hijri date display
 */
function updateHijriDate() {
    const options = {
        day: 'numeric',
        month: 'numeric',
        year: 'numeric'
    };
    
    const hijriDate = new Intl.DateTimeFormat('id-TN-u-ca-islamic', options).format(new Date());
    document.getElementById('hijri-date').textContent = hijriDate;
    
    // Update setiap 1 menit untuk sinkronisasi waktu
    setInterval(() => {
        const newDate = new Intl.DateTimeFormat('id-TN-u-ca-islamic', options).format(new Date());
        document.getElementById('hijri-date').textContent = newDate;
    }, 60000);
}

/**
 * Update prayer countdown timer
 */
function updatePrayerCountdown() {
    const nextPrayerTimeElement = document.querySelector('[data-next-prayer-time]');
    if (!nextPrayerTimeElement) return;
    
    const prayerTimeStr = nextPrayerTimeElement.getAttribute('data-next-prayer-time');
    const isTomorrow = nextPrayerTimeElement.hasAttribute('data-tomorrow');
    
    // Calculate time until next prayer
    const updateCountdown = function() {
        const now = new Date();
        const [hours, minutes] = prayerTimeStr.split(':').map(Number);
        
        let prayerTime = new Date();
        prayerTime.setHours(hours, minutes, 0, 0);
        
        if (isTomorrow) {
            prayerTime.setDate(prayerTime.getDate() + 1);
        } else if (prayerTime < now) {
            // If prayer time has passed for today, show 00:00:00
            document.getElementById('countdown').textContent = "00:00:00";
            return;
        }
        
        const diff = prayerTime - now;
        const diffHours = Math.floor(diff / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const diffSeconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        const formattedHours = String(diffHours).padStart(2, '0');
        const formattedMinutes = String(diffMinutes).padStart(2, '0');
        const formattedSeconds = String(diffSeconds).padStart(2, '0');
        
        document.getElementById('countdown').textContent = 
            `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
    };
    
    // Initial update
    updateCountdown();
    
    // Update every second
    setInterval(updateCountdown, 1000);
}

/**
 * Update next prayer time
 */
function updateNextPrayer() {
    const prayerElement = document.getElementById('next-prayer');
    if (!prayerElement) return;
    
    // This is a placeholder - in a real implementation, we would calculate
    // the next prayer time based on the user's location
    const prayers = {
        fajr: '04:45',
        sunrise: '06:01',
        dhuhr: '11:54',
        asr: '15:15',
        maghrib: '17:48',
        isha: '19:02'
    };
    
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    
    let nextPrayer = '';
    let nextPrayerTime = '';
    
    // Simple logic to determine next prayer
    // This would be more sophisticated in a real implementation
    if (currentHour < 4 || (currentHour === 4 && currentMinute < 45)) {
        nextPrayer = 'Subuh';
        nextPrayerTime = prayers.fajr;
    } else if (currentHour < 11 || (currentHour === 11 && currentMinute < 54)) {
        nextPrayer = 'Dzuhur';
        nextPrayerTime = prayers.dhuhr;
    } else if (currentHour < 15 || (currentHour === 15 && currentMinute < 15)) {
        nextPrayer = 'Ashar';
        nextPrayerTime = prayers.asr;
    } else if (currentHour < 17 || (currentHour === 17 && currentMinute < 48)) {
        nextPrayer = 'Maghrib';
        nextPrayerTime = prayers.maghrib;
    } else if (currentHour < 19 || (currentHour === 19 && currentMinute < 2)) {
        nextPrayer = 'Isya';
        nextPrayerTime = prayers.isha;
    } else {
        nextPrayer = 'Subuh';
        nextPrayerTime = prayers.fajr;
    }
    
    prayerElement.innerHTML = nextPrayer + ' ' + nextPrayerTime;
}

/**
 * Initialize smooth scrolling for navigation
 */
function initSmoothScroll() {
    var navLinks = document.querySelectorAll('a[href^="#"]:not([data-bs-toggle])');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId !== '#' && document.querySelector(targetId)) {
                e.preventDefault();
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Initialize theme toggle functionality
 */
function initThemeToggle() {
    var themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        });

        // Check stored theme preference
        if (localStorage.getItem('dark-mode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }
}

/**
 * Initialize parallax effect for hero section
 */
function initParallaxEffect() {
    var heroSection = document.getElementById('hero');
    if (heroSection) {
        window.addEventListener('scroll', function() {
            var scrollPosition = window.pageYOffset;
            if (scrollPosition < 1000) { // Limit to avoid performance issues on long scroll
                heroSection.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
            }
        });
    }
}

/**
 * Initialize database status buttons
 */
function initDBStatusButtons() {
    var dbStatusButtons = document.querySelectorAll('.check-db-btn');
    dbStatusButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var dbType = this.getAttribute('data-db');
            if (dbType === 'mongodb') {
                window.location.href = '?mongodb=check';
            } else if (dbType === 'mysql') {
                window.location.href = '?mysql=check';
            }
        });
    });
}

/**
 * Initialize back to top button
 */
function initBackToTop() {
    // Hapus tombol lama jika ada
    const oldButton = document.getElementById('back-to-top');
    if (oldButton) {
        oldButton.remove();
    }
    
    // Buat tombol baru
    const backToTopButton = document.createElement('button');
    backToTopButton.id = 'back-to-top';
    backToTopButton.className = 'btn btn-success rounded-circle shadow';
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.style.display = 'none';
    backToTopButton.setAttribute('aria-label', 'Kembali ke atas');
    backToTopButton.setAttribute('title', 'Kembali ke atas');
    
    // Pastikan tombol ditambahkan ke body, bukan di dalam footer atau elemen lain
    document.body.appendChild(backToTopButton);

    // Tampilkan tombol saat scroll melebihi 300px
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopButton.style.display = 'flex';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    // Scroll ke atas saat tombol diklik
    backToTopButton.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Cek scroll posisi saat halaman dimuat
    if (window.scrollY > 300) {
        backToTopButton.style.display = 'flex';
    }
}

/**
 * Set active navigation item based on current page
 */
function setActiveNavItem() {
    // Get current page filename
    const path = window.location.pathname;
    const currentPage = path.split('/').pop() || 'index.php'; // Default to index.php if at root
    
    // Handle direct page link active state
    const currentLinks = document.querySelectorAll(`.nav-link[href="${currentPage}"]`);
    currentLinks.forEach(link => {
        link.classList.add('active');
    });
    
    // Handle dropdown active state
    const dropdownLinks = document.querySelectorAll('.nav-link.dropdown-toggle');
    dropdownLinks.forEach(link => {
        const dropdownMenu = link.nextElementSibling;
        if (dropdownMenu) {
            const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                    item.classList.add('active');
                }
            });
        }
    });
}

/**
 * Handle specific page functionality
 */
function initPageSpecificFunctions() {
    // Qibla direction functionality
    const qiblaEl = document.getElementById('qibla-pointer');
    if (qiblaEl) {
        // If on the qibla direction page
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Calculate qibla direction
                const qiblaDirection = calculateQiblaDirection(lat, lng);
                
                // Rotate the pointer
                qiblaEl.style.transform = `rotate(${qiblaDirection}deg)`;
                
                // Update info text
                const qiblaAngleEl = document.getElementById('qibla-angle');
                if (qiblaAngleEl) {
                    qiblaAngleEl.textContent = Math.round(qiblaDirection) + '°';
                }
            }, function(error) {
                console.error('Error getting location:', error);
                // Set default direction if geolocation fails
                qiblaEl.style.transform = 'rotate(292deg)'; // Default for Indonesia
            });
        } else {
            console.error('Geolocation is not supported by this browser.');
            qiblaEl.style.transform = 'rotate(292deg)'; // Default for Indonesia
        }
    }
    
    // Prayer times page specific functionality
    const prayerTimesTable = document.querySelector('.prayer-times-table');
    if (prayerTimesTable) {
        // Update prayer times table based on user's location
        // This would be expanded in a real implementation to use actual calculations
    }
    
    // Handle form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger mt-3';
                errorMessage.textContent = 'Harap isi semua kolom yang diperlukan.';
                
                // Remove any existing error messages
                const existingError = form.querySelector('.alert-danger');
                if (existingError) {
                    existingError.remove();
                }
                
                form.appendChild(errorMessage);
            }
        });
    });
}

/**
 * Calculate Qibla direction
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @returns {number} - Direction in degrees
 */
function calculateQiblaDirection(lat, lng) {
    // Coordinates of the Kaaba
    const kaabaLat = 21.422487;
    const kaabaLng = 39.826206;
    
    // Convert to radians
    const latRad = lat * Math.PI / 180;
    const lngRad = lng * Math.PI / 180;
    const kaabaLatRad = kaabaLat * Math.PI / 180;
    const kaabaLngRad = kaabaLng * Math.PI / 180;
    
    // Calculate qibla direction
    const y = Math.sin(kaabaLngRad - lngRad);
    const x = Math.cos(latRad) * Math.tan(kaabaLatRad) - Math.sin(latRad) * Math.cos(kaabaLngRad - lngRad);
    let qibla = Math.atan2(y, x) * 180 / Math.PI;
    
    // Normalize to 0-360
    qibla = (qibla + 360) % 360;
    
    return qibla;
}

// Fungsi untuk dropdown hover (desktop) dan toggle (mobile)
function handleDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        // Hover untuk desktop
        dropdown.addEventListener('mouseenter', () => {
            if (window.innerWidth >= 992) { // Sesuai breakpoint LG Bootstrap
                dropdown.classList.add('show');
                dropdown.querySelector('.dropdown-toggle').click();
            }
        });
        
        dropdown.addEventListener('mouseleave', () => {
            if (window.innerWidth >= 992) {
                dropdown.classList.remove('show');
                dropdown.querySelector('.dropdown-toggle').click();
            }
        });
    });
}

// Mobile menu close saat klik item
function handleMobileMenu() {
    const navbarCollapse = document.getElementById('navbarNav');
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                navbarCollapse.classList.remove('show');
            }
        });
    });
}

// Inisialisasi semua fungsi
document.addEventListener('DOMContentLoaded', () => {
    handleDropdowns();
    updateHijriDate();
    initBackToTop();
    handleMobileMenu();
    
    // Handle window resize untuk dropdown
    window.addEventListener('resize', handleDropdowns);
});

// Tim Pengajar Gallery Filter
function initTeacherGallery() {
    const filterButtons = document.querySelectorAll('.teacher-filter');
    const teacherItems = document.querySelectorAll('.teacher-item');
    
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get filter value
                const filterValue = this.getAttribute('data-filter');
                
                // Filter items
                teacherItems.forEach(item => {
                    if (filterValue === 'all' || item.classList.contains(filterValue)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }
}

// Testimonial Carousel
function initTestimonialCarousel() {
    const testimonialItems = document.querySelectorAll('.testimonial-item');
    const prevButton = document.querySelector('.testimonial-prev');
    const nextButton = document.querySelector('.testimonial-next');
    const dots = document.querySelectorAll('.testimonial-dot');
    
    if (testimonialItems.length === 0) return;
    
    let currentSlide = 0;
    
    // Function to show specific slide
    const showSlide = (index) => {
        // Hide all slides
        testimonialItems.forEach(item => {
            item.classList.remove('active');
        });
        
        // Remove active class from all dots
        dots.forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Show current slide
        testimonialItems[index].classList.add('active');
        dots[index].classList.add('active');
    };
    
    // Event listeners for navigation buttons
    if (prevButton) {
        prevButton.addEventListener('click', () => {
            currentSlide--;
            if (currentSlide < 0) {
                currentSlide = testimonialItems.length - 1;
            }
            showSlide(currentSlide);
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            currentSlide++;
            if (currentSlide >= testimonialItems.length) {
                currentSlide = 0;
            }
            showSlide(currentSlide);
        });
    }
    
    // Event listeners for dot indicators
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });
    
    // Auto slide (optional)
    let autoSlide = setInterval(() => {
        currentSlide++;
        if (currentSlide >= testimonialItems.length) {
            currentSlide = 0;
        }
        showSlide(currentSlide);
    }, 5000);
    
    // Pause auto slide on hover
    const testimonialCarousel = document.querySelector('.testimonial-carousel');
    if (testimonialCarousel) {
        testimonialCarousel.addEventListener('mouseenter', () => {
            clearInterval(autoSlide);
        });
        
        testimonialCarousel.addEventListener('mouseleave', () => {
            autoSlide = setInterval(() => {
                currentSlide++;
                if (currentSlide >= testimonialItems.length) {
                    currentSlide = 0;
                }
                showSlide(currentSlide);
            }, 5000);
        });
    }
    
    // Show first slide initially
    showSlide(currentSlide);
} 