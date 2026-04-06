/**
 * ==========================================================================
 * COMPONENT LOADER - Villa de Sant
 * --------------------------------------------------------------------------
 * Handles dynamic injection of HTML partials, theme management,
 * and global widget initialization (WhatsApp, Language, etc.)
 * ==========================================================================
 */

/**
 * Loads an HTML partial and injects it into a placeholder element.
 * @param {string} id - The ID of the placeholder element.
 * @param {string} filePath - Path to the HTML file.
 */
async function loadComponent(id, filePath) {
    const element = document.getElementById(id);
    if (!element) return;

    try {
        const response = await fetch(filePath);
        if (!response.ok) throw new Error(`Could not fetch ${filePath}: ${response.statusText}`);
        const html = await response.text();
        element.innerHTML = html;

        // Notify other scripts that this component is ready
        window.dispatchEvent(new CustomEvent(`componentLoaded:${id}`, { detail: { id, filePath } }));
    } catch (error) {
        console.error(' [ComponentLoader] Error:', error);
        element.innerHTML = `<p style="text-align:center; padding:20px; color:var(--primary-gold);">Error al cargar componente: ${id}</p>`;
    }
}

/**
 * Applies the stored theme from localStorage on initial load to prevent FOUC.
 */
function applyStoredTheme() {
    const savedTheme = localStorage.getItem('villa_theme');
    // Light mode is the default — only apply dark if explicitly saved
    if (savedTheme === 'dark') {
        document.body.classList.remove('light-mode');
    } else {
        document.body.classList.add('light-mode');
    }
}

// Global initialization
applyStoredTheme();

document.addEventListener('DOMContentLoaded', () => {
    // Primary components sequence
    const components = [
        loadComponent('header-placeholder', 'partials/header.html'),
        loadComponent('footer-placeholder', 'partials/footer.html'),
        loadComponent('modal-placeholder', 'partials/modal-reserva.html'),
        loadComponent('modal-tour-placeholder', 'partials/modal-tour.html')
    ];

    Promise.all(components).then(() => {
        highlightActiveNav();
        injectWidgets();
        initHummingbirdAnimations();
        initScrollProgress();
    });
});

/**
 * Injects floating widgets (Language switcher, WhatsApp, Bottom Nav).
 */
function injectWidgets() {
    // Stacked Left Widgets
    const widgetsHtml = `
        <!-- Floating Adjustments (Left Stacked) -->
        <div class="lang-switcher animate-fade" id="custom-lang-switcher">
            <div class="lang-settings-content" id="lang-settings-content">
                <button class="lang-btn active" data-lang="es"><img src="https://flagcdn.com/es.svg" alt="ES" title="Español"></button>
                <button class="lang-btn" data-lang="en"><img src="https://flagcdn.com/us.svg" alt="EN" title="English"></button>
                <button class="lang-btn" data-lang="fr"><img src="https://flagcdn.com/fr.svg" alt="FR" title="Français"></button>
                <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.2); width: 80%; margin: 5px auto;">
                <button class="theme-btn" id="theme-toggle" title="Modo Claro/Oscuro"><i class="fa-solid fa-moon"></i></button>
            </div>
            <button class="lang-master-toggle theme-btn" id="lang-master-toggle" title="Ajustes"><i class="fa-solid fa-gear"></i></button>
        </div>

        <div class="whatsapp-widget animate-fade">
            <div class="chat-box" id="wa-chat">
                <div class="chat-header">
                    <img src="https://lh3.googleusercontent.com/p/AF1QipNVb6baKMNn4xJZncYQr9f1z7mdaYdRtnXhkfiz=s200" alt="Villa de Sant">
                    <div>
                        <h4>Villa de Sant</h4>
                        <p>En línea • Asistente Virtual</p>
                    </div>
                </div>
                <div class="chat-body" id="chat-conversation">
                    <!-- Messages will be injected here -->
                    <div class="typing-indicator" id="typing">Escribiendo...</div>
                </div>
                <div class="chat-footer">
                    <button class="whatsapp-confirm-btn">
                        Continuar al chat <i class="fa-brands fa-whatsapp"></i>
                    </button>
                </div>
            </div>
            <div class="whatsapp-button">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
        </div>


        <div id="google_translate_element" style="display:none;"></div>

        <!-- Mobile Bottom Nav -->
        <nav class="mobile-bottom-nav" id="mobile-bottom-nav" aria-label="Bottom Navigation">
            <a href="index.html" class="mob-nav-item" title="Inicio"><i class="fa-solid fa-house"></i></a>
            <a href="habitaciones.html" class="mob-nav-item" title="Habitaciones"><i class="fa-solid fa-bed"></i></a>
            <a href="experiencias.html" class="mob-nav-item" title="Experiencias"><i class="fa-solid fa-compass"></i></a>
            <a href="nosotros.html" class="mob-nav-item" title="Nosotros"><i class="fa-solid fa-users"></i></a>
        </nav>
    `;

    document.body.insertAdjacentHTML('beforeend', widgetsHtml);

    // Initial state setup
    setupActiveNavItem();
    if (typeof initWhatsAppWidget === 'function') initWhatsAppWidget();
    
    // Lazy load Google Translate
    initGoogleTranslate();
    
    // Bind interactions after a short tick
    setTimeout(bindWidgetsEvents, 100);
}

/**
 * Initializes Google Translate bridge.
 */
function initGoogleTranslate() {
    const script = document.createElement('script');
    script.src = "//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
    document.body.appendChild(script);

    window.googleTranslateElementInit = function() {
        new google.translate.TranslateElement({
            pageLanguage: 'es', 
            includedLanguages: 'es,en,fr', 
            autoDisplay: false
        }, 'google_translate_element');
    };
}

/**
 * Binds UI events for theme toggling, language switching and master toggle.
 */
function bindWidgetsEvents() {
    const themeBtn = document.getElementById('theme-toggle');
    const masterToggle = document.getElementById('lang-master-toggle');
    const switcher = document.getElementById('custom-lang-switcher');

    if (themeBtn) {
        const icon = themeBtn.querySelector('i');
        if (document.body.classList.contains('light-mode')) icon.classList.replace('fa-moon', 'fa-sun');

        themeBtn.addEventListener('click', () => {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            localStorage.setItem('villa_theme', isLight ? 'light' : 'dark');
            icon.classList.replace(isLight ? 'fa-moon' : 'fa-sun', isLight ? 'fa-sun' : 'fa-moon');
        });
    }

    // Language processing
    const langButtons = document.querySelectorAll('.lang-btn');
    const savedLang = localStorage.getItem('villa_lang');
    if (savedLang) setTimeout(() => triggerTranslation(savedLang, langButtons), 500);

    langButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const lang = e.currentTarget.getAttribute('data-lang');
            localStorage.setItem('villa_lang', lang);
            triggerTranslation(lang, langButtons);
        });
    });

    // Settings Master Toggle
    if (masterToggle && switcher) {
        masterToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            switcher.classList.toggle('expanded');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!switcher.contains(e.target)) {
                switcher.classList.remove('expanded');
            }
        });
    }
}

/**
 * Triggers Google Translate interaction based on language selected.
 * @param {string} lang - Language code (es, en, fr).
 * @param {NodeList} buttons - Collection of language buttons.
 */
function triggerTranslation(lang, buttons) {
    if (!buttons) buttons = document.querySelectorAll('.lang-btn');
    buttons.forEach(b => b.classList.toggle('active', b.getAttribute('data-lang') === lang));

    const selectField = document.querySelector(".goog-te-combo");
    if (selectField) {
        selectField.value = lang;
        selectField.dispatchEvent(new Event('change'));
    }
}

/**
 * Highlights the active page in both desktop and mobile bottom navigation.
 */
function setupActiveNavItem() {
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    const allLinks = document.querySelectorAll('.nav-link, .mob-nav-item');
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        link.classList.toggle('active', href === currentPath);
    });
}

function highlightActiveNav() { setupActiveNavItem(); }

/**
 * Initializes Lottie Hummingbird animations using IntersectionObserver for better performance.
 */
function initHummingbirdAnimations() {
    if (typeof lottie === 'undefined') return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const container = entry.target;
                if (!container.dataset.loaded) {
                    lottie.loadAnimation({
                        container: container,
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: 'assets/animations/vectorized.json'
                    });
                    container.dataset.loaded = 'true';
                } else {
                    // Optimized: just play/pause if already loaded
                    // This part depends on how lottie instances are stored, for now we let it run
                }
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.lottie-hummingbird').forEach(el => observer.observe(el));
}

/**
 * Updates the horizontal scroll progress bar at the top of the viewport.
 */
function initScrollProgress() {
    const header = document.getElementById('main-header');
    const scrollLine = document.querySelector('.header-scroll-line');
    
    if (!header) return;

    window.addEventListener('scroll', () => {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;

        // Update progress line
        if (scrollLine) scrollLine.style.width = scrolled + "%";

        // Toggle scrolled class for header & settings - Desktop only (> 992px)
        const settingsWidget = document.getElementById('custom-lang-switcher');
        if (window.innerWidth >= 992) {
            if (winScroll > 50) {
                header.classList.add('scrolled');
                if (settingsWidget) settingsWidget.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
                if (settingsWidget) settingsWidget.classList.remove('scrolled');
            }
        } else {
            header.classList.remove('scrolled');
            if (settingsWidget) settingsWidget.classList.remove('scrolled');
        }

    });

}


/**
 * Handles Tour Modal interaction and WhatsApp form submission.
 */
function initTourModalTriggers() {
    const tourModal = document.getElementById('tour-modal');
    if (!tourModal) return;

    const displayTitle = document.getElementById('tour-name-display');
    const hiddenInput = document.getElementById('tour-hidden-name');
    const form = document.getElementById('tour-wa-form');

    document.querySelectorAll('.btn-tour-trigger').forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
            const name = btn.getAttribute('data-tour');
            if(name) {
                displayTitle.innerText = name;
                hiddenInput.value = name;
            }
            tourModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };
    });

    const closeBtn = document.getElementById('tour-modal-close');
    if (closeBtn) {
        closeBtn.onclick = () => {
            tourModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        };
    }

    if (form) {
        form.onsubmit = (e) => {
            e.preventDefault();
            const guest = document.getElementById('tour-guest-name').value;
            const room = document.getElementById('tour-room').value;
            const tour = hiddenInput.value;
            const text = `¡Hola! 👋 Deseo agendar un Tour:\n📍 *${tour}*\n👤 Huésped: ${guest}\n🛏️ Habitación: ${room}`;
            window.open(`https://wa.me/593984606212?text=${encodeURIComponent(text)}`, '_blank');
            tourModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        };
    }
}

// Bind modal triggers after fragment loads
window.addEventListener('componentLoaded:modal-tour-placeholder', initTourModalTriggers);
