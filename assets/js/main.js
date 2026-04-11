/**
 * Main Interactivity - Villa de Sant
 */

document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initMenu();
    initModalTriggers();
});

function initScrollAnimations() {
    const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            } else {
                entry.target.classList.remove('is-visible');
            }
        });
    }, observerOptions);
    document.querySelectorAll('.scroll-anim').forEach((el) => observer.observe(el));
}

function initMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const closeOverlay = document.getElementById('close-overlay');
    const mobileOverlay = document.getElementById('mobile-overlay');
    if (menuToggle) menuToggle.onclick = () => mobileOverlay.classList.add('active');
    if (closeOverlay) closeOverlay.onclick = () => mobileOverlay.classList.remove('active');
}

function initModalTriggers() {
    const triggers = document.querySelectorAll('.btn-book-trigger');
    triggers.forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
            const baseUrl = document.querySelector('base') ? document.querySelector('base').href : '/';
            // In our PHP structure, reserva.php is in views/usuario/
            // If we're on a page inside views/usuario/, it's just reserva.php
            // If we're on index.php (root), it's views/usuario/reserva.php
            const currentPath = window.location.pathname;
            const isUserView = currentPath.includes('/views/usuario/');
            window.location.href = isUserView ? 'reserva.php' : 'views/usuario/reserva.php';
        };
    });
}

function sendWhatsAppMessage(customMessage = null) {
    const number = "593984606212";
    let message = customMessage || "Hola Villa de Sant! 👋 Estoy interesado en realizar una reserva o necesito más información.";
    const whatsappUrl = `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}
