/**
 * WhatsApp Chatbot & Widget Logic - Villa de Sant
 */

function initWhatsAppWidget() {
    const whatsappButton = document.querySelector('.whatsapp-button');
    const chatBox = document.querySelector('.chat-box');
    const confirmBtn = document.querySelector('.whatsapp-confirm-btn');
    const conversation = document.getElementById('chat-conversation');
    const typingIndicator = document.getElementById('typing');

    if (!whatsappButton || !chatBox) return;

    let hasOpened = false;

    whatsappButton.onclick = () => {
        chatBox.classList.toggle('active');
        
        if (chatBox.classList.contains('active') && !hasOpened) {
            startBotSequence();
            hasOpened = true; 
        }
    };

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
        confirmBtn.onclick = () => {
            sendWhatsAppMessage();
        };
    }
}

function sendWhatsAppMessage() {
    const number = "593984606212";
    let message = "Hola Villa de Sant! 👋 Estoy interesado en realizar una reserva o necesito más información.";

    const roomSelect = document.querySelector('select');
    const checkIn = document.querySelectorAll('input[type="date"]')[0];
    const checkOut = document.querySelectorAll('input[type="date"]')[1];
    
    if (roomSelect && checkIn && checkIn.value !== "") {
        message = `Hola Villa de Sant! 👋\nQuisiera información sobre disponibilidad:\n🏨 Tipo: ${roomSelect.value}\n📅 Check-in: ${checkIn.value}`;
    }

    const whatsappUrl = `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

