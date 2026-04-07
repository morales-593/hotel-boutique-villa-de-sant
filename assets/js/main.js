/**
 * Main Interactivity - Villa de Sant
 */

document.addEventListener('DOMContentLoaded', () => {
    // Wait for components to load before binding events
    window.addEventListener('componentLoaded:header-placeholder', initMenu);
    window.addEventListener('componentLoaded:modal-placeholder', initModalTriggers);
    
    initScrollAnimations();
});

function initScrollAnimations() {
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            } else {
                entry.target.classList.remove('is-visible'); // Permite desaparecer y aparecer constantemente
            }
        });
    }, observerOptions);

    document.querySelectorAll('.scroll-anim').forEach((el) => {
        observer.observe(el);
    });
}

function initMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const closeOverlay = document.getElementById('close-overlay');
    const mobileOverlay = document.getElementById('mobile-overlay');

    if (menuToggle) {
        menuToggle.onclick = () => mobileOverlay.classList.add('active');
    }

    if (closeOverlay) {
        closeOverlay.onclick = () => mobileOverlay.classList.remove('active');
    }
}

function initModalTriggers() {
    const modal = document.getElementById('booking-modal');
    const modalClose = document.getElementById('modal-close');
    const triggers = document.querySelectorAll('.btn-book-trigger');

    triggers.forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
            const viewsPath = window.location.pathname.includes('/views/') ? './' : 'views/';
            window.location.href = viewsPath + 'reserva.html';
        };
    });

    if (modalClose) {
        modalClose.onclick = () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        };
    }

    // Close on outside click
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
}
