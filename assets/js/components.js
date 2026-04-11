/**
 * ==========================================================================
 * COMPONENT LOADER - Villa de Sant
 * --------------------------------------------------------------------------
 * Handles theme management, translation and global widget initialization
 * ==========================================================================
 */

function applyStoredTheme() {
    const savedTheme = localStorage.getItem('villa_theme');
    if (savedTheme === 'dark') {
        document.body.classList.remove('light-mode');
    } else {
        document.body.classList.add('light-mode');
    }
}

applyStoredTheme();

document.addEventListener('DOMContentLoaded', () => {
    const isView = window.location.pathname.includes('/views/');
    const assetsPath = isView ? '../assets/' : 'assets/';
    const rootPath = isView ? '../' : './';
    const viewsPath = isView ? './' : 'views/';

    injectWidgets(rootPath, viewsPath);
    initHummingbirdAnimations(assetsPath);
    initScrollProgress();
    initTourModalTriggers();
});

/**
 * Injects floating widgets (Language switcher, WhatsApp, Bottom Nav).
 */
function injectWidgets(rootPath, viewsPath) {
    const widgetsHtml = `
        <div class="lang-switcher animate-fade" id="custom-lang-switcher">
            <div class="lang-settings-content" id="lang-settings-content">
                <button class="lang-btn active" data-lang="es"><img src="https://flagcdn.com/es.svg" alt="ES" title="Español"></button>
                <button class="lang-btn" data-lang="en"><img src="https://flagcdn.com/us.svg" alt="EN" title="English"></button>
                <button class="lang-btn" data-lang="fr"><img src="https://flagcdn.com/fr.svg" alt="FR" title="Français"></button>
                <button class="lang-btn" data-lang="de"><img src="https://flagcdn.com/de.svg" alt="DE" title="Deutsch"></button>
                <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.2); width: 80%; margin: 5px auto;">
                <button class="theme-btn" id="theme-toggle" title="Modo Claro/Oscuro"><i class="fa-solid fa-moon"></i></button>
            </div>
            <button class="lang-master-toggle theme-btn" id="lang-master-toggle" title="Ajustes"><i class="fa-solid fa-gear"></i></button>
        </div>
        <div id="google_translate_element" style="display:none;"></div>
    `;

    document.body.insertAdjacentHTML('beforeend', widgetsHtml);
    initGoogleTranslate();
    setTimeout(bindWidgetsEvents, 100);
}

function initGoogleTranslate() {
    const script = document.createElement('script');
    script.src = "//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
    document.body.appendChild(script);
    window.googleTranslateElementInit = function() {
        new google.translate.TranslateElement({
            pageLanguage: 'es', 
            includedLanguages: 'es,en,fr,de', 
            autoDisplay: false
        }, 'google_translate_element');
    };
}

function bindWidgetsEvents() {
    const themeBtn = document.getElementById('theme-toggle');
    const masterToggle = document.getElementById('lang-master-toggle');
    const switcher = document.getElementById('custom-lang-switcher');
    const langButtons = document.querySelectorAll('.lang-btn');

    if (themeBtn) {
        const icon = themeBtn.querySelector('i');
        if (document.body.classList.contains('light-mode')) icon.classList.replace('fa-moon', 'fa-sun');
        themeBtn.onclick = () => {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            localStorage.setItem('villa_theme', isLight ? 'light' : 'dark');
            icon.classList.replace(isLight ? 'fa-moon' : 'fa-sun', isLight ? 'fa-sun' : 'fa-moon');
        };
    }

    if (masterToggle) {
        masterToggle.onclick = (e) => {
            e.stopPropagation();
            switcher.classList.toggle('expanded');
        };
    }

    // Language processing
    langButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const lang = e.currentTarget.getAttribute('data-lang');
            triggerTranslation(lang, langButtons);
        });
    });

    // Close settings when clicking outside
    document.addEventListener('click', (e) => {
        if (switcher && !switcher.contains(e.target)) {
            switcher.classList.remove('expanded');
        }
    });
}

function triggerTranslation(lang, buttons) {
    buttons.forEach(b => b.classList.toggle('active', b.getAttribute('data-lang') === lang));
    const selectField = document.querySelector(".goog-te-combo");
    if (selectField) {
        selectField.value = lang;
        selectField.dispatchEvent(new Event('change'));
    }
}

function initHummingbirdAnimations(assetsPath) {
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
                        path: assetsPath + 'animations/vectorized.json'
                    });
                    container.dataset.loaded = 'true';
                }
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.lottie-hummingbird').forEach(el => observer.observe(el));
}

function initScrollProgress() {
    const header = document.getElementById('main-header');
    const scrollLine = document.querySelector('.header-scroll-line');
    if (!header) return;
    window.addEventListener('scroll', () => {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        if (scrollLine) scrollLine.style.width = scrolled + "%";
        if (winScroll > 50) header.classList.add('scrolled');
        else header.classList.remove('scrolled');
    });
}

function initTourModalTriggers() {
    const tourModal = document.getElementById('tour-modal');
    if (!tourModal) return;
    document.querySelectorAll('.btn-tour-trigger').forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
            const name = btn.getAttribute('data-tour');
            if(name) {
                document.getElementById('tour-name-display').innerText = name;
                document.getElementById('tour-hidden-name').value = name;
            }
            tourModal.style.display = 'flex';
        };
    });
    const closeBtn = document.getElementById('tour-modal-close');
    if (closeBtn) closeBtn.onclick = () => tourModal.style.display = 'none';
}
