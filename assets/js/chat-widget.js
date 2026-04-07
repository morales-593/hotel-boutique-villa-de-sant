/**
 * WhatsApp Chatbot & Widget Logic - Villa de Sant
 */

function initWhatsAppWidget() {
    const whatsappButton = document.querySelector('.whatsapp-button');
    const chatBox = document.querySelector('.chat-box');
    const confirmBtn = document.querySelector('.whatsapp-confirm-btn');
    const conversation = document.getElementById('chat-conversation');
    const typingIndicator = document.getElementById('typing');

    if (!whatsappButton || !chatBox) {
        console.warn('WhatsApp elements not found');
        return;
    }

    let hasOpened = false;

    // Use eventListener for maximum compatibility
    whatsappButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        chatBox.classList.toggle('active');
        
        if (chatBox.classList.contains('active') && !hasOpened) {
            startBotSequence();
            hasOpened = true; 
        }
    });

    function startBotSequence() {
        const messages = [
            "¡Hola! 👋 Bienvenido a Villa de Sant.",
            "Soy tu asistente virtual, ¿en qué puedo ayudarte hoy?",
            "Puedo ayudarte con información de habitaciones, tours o reservas."
        ];

        let delay = 500;

        messages.forEach((msg, index) => {
            setTimeout(() => {
                showTyping();
                setTimeout(() => {
                    hideTyping();
                    addMessage(msg);
                }, 1500);
            }, delay);
            delay += 2500;
        });
    }

    function addMessage(text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'msg msg-bot';
        msgDiv.innerText = text;
        conversation.insertBefore(msgDiv, typingIndicator);
        conversation.scrollTop = conversation.scrollHeight;
    }

    function showTyping() {
        if (typingIndicator) typingIndicator.style.display = 'block';
    }

    function hideTyping() {
        if (typingIndicator) typingIndicator.style.display = 'none';
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', (e) => {
            e.preventDefault();
            sendWhatsAppMessage();
        });
    }
}

function sendWhatsAppMessage(customMessage = null) {
    const number = "593984606212";
    let message = "Hola Villa de Sant! 👋 Estoy interesado en realizar una reserva o necesito más información.";

    if (customMessage) {
        message = customMessage;
    } else {
        const fullForm = document.getElementById('full-booking-form');
        
        if (fullForm) {
            // We are on reserva.html, extract full details
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
            // General pages fallback
            const roomSelect = document.querySelector('select');
            const checkIn = document.querySelectorAll('input[type="date"]')[0];
            
            if (roomSelect && checkIn && checkIn.value !== "") {
                message = `Hola Villa de Sant! 👋\nQuisiera información sobre disponibilidad:\n🏨 Tipo: ${roomSelect.value}\n📅 Check-in: ${checkIn.value}`;
            }
        }
    }

    const whatsappUrl = `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    
    // Fallback opening method if window.open is blocked
    const link = document.createElement('a');
    link.href = whatsappUrl;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

