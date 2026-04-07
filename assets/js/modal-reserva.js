/**
 * Booking Modal Logic - Villa de Sant
 */

document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('componentLoaded:modal-placeholder', initBookingForm);
});

function initBookingForm() {
    const form = document.getElementById('quick-booking-form');
    const toast = document.getElementById('toast');
    const modal = document.getElementById('booking-modal');

    if (!form) return;

    form.onsubmit = (e) => {
        e.preventDefault();
        const btn = form.querySelector('.btn-gold');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<span class="loading-spinner">...</span>';
        btn.disabled = true;

        // Simulate API call
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            // Show Success
            toast.classList.add('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';

            setTimeout(() => {
                toast.classList.remove('show');
                form.reset();
            }, 3000);
        }, 1500);
    };
}
