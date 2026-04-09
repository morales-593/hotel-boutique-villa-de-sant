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

// ==========================================
// WhatsApp Chatbot & Widget Logic
// ==========================================

function sendWhatsAppMessage(customMessage = null) {
    const number = "593984606212";
    let message = "Hola Villa de Sant! 👋 Estoy interesado en realizar una reserva o necesito más información.";

    if (customMessage) {
        message = customMessage;
    } else {
        const fullForm = document.getElementById('full-booking-form');
        
        if (fullForm) {
            const nameInput = fullForm.querySelectorAll('input[type="text"]')[0];
            const lastNameInput = fullForm.querySelectorAll('input[type="text"]')[1];
            const guestsInput = fullForm.querySelector('input[type="number"]');
            const roomSelect = fullForm.querySelector('select');
            const checkInInput = fullForm.querySelectorAll('input[type="date"]')[0];
            const checkOutInput = fullForm.querySelectorAll('input[type="date"]')[1];
            const summaryTotal = document.querySelector('.summary-total span:last-child');
            
            const fullName = `${nameInput ? nameInput.value : ''} ${lastNameInput ? lastNameInput.value : ''}`.trim();
            const room = roomSelect ? roomSelect.value : '';
            const checkIn = checkInInput ? checkInInput.value : '';
            const checkOut = checkOutInput ? checkOutInput.value : '';
            const totalVal = summaryTotal ? summaryTotal.innerText : '';
            
            message = `¡Hola Villa de Sant! 👋\nDeseo confirmar/consultar mi reserva:\n👤 Nombre: ${fullName || 'Pendiente'}\n👥 Huéspedes: ${guestsInput ? guestsInput.value : '2'}\n🏨 Habitación: ${room}\n📅 Check-in: ${checkIn}\n📅 Check-out: ${checkOut}\n💵 Total Estimado: ${totalVal}`;
        } else {
            const roomSelect = document.querySelector('select');
            const dateInputs = document.querySelectorAll('input[type="date"]');
            const checkIn = dateInputs.length > 0 ? dateInputs[0] : null;
            
            if (roomSelect && checkIn && checkIn.value !== "") {
                message = `Hola Villa de Sant! 👋\nQuisiera información sobre disponibilidad:\n🏨 Tipo: ${roomSelect.value}\n📅 Check-in: ${checkIn.value}`;
            }
        }
    }

    const whatsappUrl = `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    const link = document.createElement('a');
    link.href = whatsappUrl;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
