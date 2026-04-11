<?php

require_once "config/config.php";
$pageTitle = "Hotel Boutique Villa de Sant | Inicio";
$extraCSS = '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/home.css">';
include_once "views/layouts/header.php";
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content scroll-anim is-visible">
            <h1 class="hero-title">Santuario Colonial en el Corazón de Quito</h1>
            <p class="hero-subtitle">Vive una estancia inolvidable en nuestro hotel boutique, donde el lujo histórico florece bajo el vuelo del colibrí.</p>
            <div class="hero-actions">
                <a href="reserva.php" class="btn btn-gold">RESERVA TU EXPERIENCIA</a>
            </div>
        </div>
    </section>

    <!-- SECTION: El Vuelo del Colibrí (Now ABOVE Amenities) -->
    <section class="section-padding story-section">
        <div class="container story-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center;">
            <div class="story-image scroll-anim scroll-left" style="border-radius: 20px; overflow: hidden; height: 600px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                <img src="https://lh3.googleusercontent.com/p/AF1QipNVb6baKMNn4xJZncYQr9f1z7mdaYdRtnXhkfiz=s4000" alt="Villa de Sant Interior" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div class="story-content scroll-anim scroll-right">
                <h2 class="gold-text serif">El Vuelo del Colibrí</h2>
                <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 25px;">En el corazón de Quito, surge Villa de Sant como un homenaje a la agilidad y la elegancia de nuestra ave más icónica. Nuestra historia se teje entre muros coloniales y un servicio contemporáneo que fluye sin esfuerzo.</p>
                <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 40px;">Cada rincón de nuestro hotel ha sido diseñado para transportarte a una época de esplendor, sin renunciar a las comodidades del lujo moderno. Descubre por qué somos el refugio favorito de quienes buscan algo más que un simple alojamiento.</p>
                <a href="habitaciones.php" class="btn btn-gold" style="padding: 18px 40px; border-radius: 4px; font-weight: 700;">EXPLORAR HABITACIONES</a>
            </div>
        </div>
    </section>

    <!-- Amenities Dual Carousel -->
    <section class="amenities-marquee-section">
        <div class="container text-center" style="padding-bottom: 30px;">
            <p style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 3px; font-size: 0.75rem; font-weight: 700; margin-bottom: 8px;">
                <i class="fa-solid fa-star" style="margin-right: 6px;"></i>SERVICIOS INCLUIDOS
            </p>
            <h2 class="serif gold-text" style="font-size: 2rem; margin-bottom: 6px;">Comodidades Exclusivas</h2>
        </div>

        <?php 
        $amenities = [
            ['icon' => 'fa-wifi', 'label' => 'Wi-Fi gratis'],
            ['icon' => 'fa-mug-hot', 'label' => 'Desayuno incluido'],
            ['icon' => 'fa-square-parking', 'label' => 'Estacionamiento'],
            ['icon' => 'fa-ban-smoking', 'label' => 'Libre de humo'],
            ['icon' => 'fa-snowflake', 'label' => 'Aire acondicionado'],
            ['icon' => 'fa-shirt', 'label' => 'Lavandería'],
            ['icon' => 'fa-paw', 'label' => 'Mascotas'],
            ['icon' => 'fa-utensils', 'label' => 'Restaurante'],
        ];
        ?>

        <div class="amenity-track-wrapper" style="margin-bottom: 15px;">
            <div class="amenity-track track-left">
                <?php for($i=0; $i<3; $i++){ foreach($amenities as $a){ echo '<div class="amenity-chip"><i class="fa-solid '.$a['icon'].'"></i><span>'.$a['label'].'</span></div>'; } } ?>
            </div>
        </div>
        <div class="amenity-track-wrapper">
            <div class="amenity-track track-right">
                <?php for($i=0; $i<3; $i++){ foreach(array_reverse($amenities) as $a){ echo '<div class="amenity-chip chip-outline"><i class="fa-solid '.$a['icon'].'"></i><span>'.$a['label'].'</span></div>'; } } ?>
            </div>
        </div>
    </section>

    <!-- Reviews Marquee -->
    <section class="section-padding reviews-carousel-section">
        <div class="container text-center">
            <h2 class="gold-text">Experiencias Memorables</h2>
            <p style="color: var(--text-gray); margin-bottom: 50px;">Lo que dicen nuestros huéspedes en todo el mundo.</p>
        </div>
        <div class="marquee-container">
            <div class="marquee-content">
                <?php 
                $reviews = [
                    ['stars' => 5, 'text' => "Un oasis absoluto en medio de la ciudad histórica. Minimalista y elegante.", 'author' => "Marie L.", 'loc' => "Francia"],
                    ['stars' => 5, 'text' => "La atención y el lujo en cada detalle hicieron nuestra estadía inolvidable.", 'author' => "John D.", 'loc' => "USA"],
                ];
                for($i=0; $i<5; $i++){ foreach($reviews as $r){
                    echo '<div class="review-box">
                        <div class="stars">'.str_repeat('<i class="fa-solid fa-star"></i>', $r['stars']).'</div>
                        <p class="review-text">"'.$r['text'].'"</p>
                        <p class="review-author">'.$r['author'].'<br><span>'.$r['loc'].'</span></p>
                    </div>';
                } }
                ?>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="section-padding story-section">
        <div class="container">
            <div class="info-split-grid">
                <div class="amenities-side">
                    <h2 class="gold-text">Santuario de Comodidades</h2>
                    <div class="schedule-cards">
                        <div class="schedule-card"><div class="schedule-icon"><i class="fa-solid fa-bell-concierge"></i></div><h4>Hora de entrada</h4><p>12:00 p. m.</p></div>
                        <div class="schedule-card"><div class="schedule-icon"><i class="fa-solid fa-key"></i></div><h4>Hora de salida</h4><p>12:00 p. m.</p></div>
                    </div>
                </div>
                <div class="faq-side">
                    <h2 class="gold-text">Preguntas Frecuentes</h2>
                    <div class="faq-grid">
                        <div class="faq-item">
                            <div class="faq-question">¿Están céntricos? <i class="fa-solid fa-chevron-down"></i></div>
                            <div class="faq-answer">Sí, estamos ubicados estratégicamente en el corazón de Quito, permitiéndole acceder fácilmente a los principales puntos culturales y financieros de la ciudad.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">¿Están cerca del mercado Artesanal de la Mariscal? <i class="fa-solid fa-chevron-down"></i></div>
                            <div class="faq-answer">Nos encontramos en una ubicación privilegiada, a pocos minutos del emblemático Mercado Artesanal de La Mariscal, ideal para descubrir artesanías y cultura local.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">¿Cuanto tiempo se hace desde el hotel al centro Histórico? <i class="fa-solid fa-chevron-down"></i></div>
                            <div class="faq-answer">El trayecto es sumamente rápido, toma aproximadamente entre 5 a 10 minutos en vehículo, conectándolo con la majestuosidad del Centro Histórico de Quito.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">¿Hay restaurantes abiertos las 24 horas? <i class="fa-solid fa-chevron-down"></i></div>
                            <div class="faq-answer">La zona circundante ofrece diversas opciones gastronómicas, incluyendo lugares con horarios extendidos muy cerca de nuestras instalaciones.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">¿Tienen restaurante las 24 horas en el hotel? <i class="fa-solid fa-chevron-down"></i></div>
                            <div class="faq-answer">Nuestro restaurante ofrece servicio gourmet durante horarios establecidos. Además, contamos con atención de recepción permanente para asistirle con pedidos externos.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', () => {
                const faqItem = button.parentElement;
                const isOpen = faqItem.classList.contains('active');
                
                // Close all other items
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                    const icon = item.querySelector('i');
                    if(icon) icon.className = 'fa-solid fa-chevron-down';
                });

                if (!isOpen) {
                    faqItem.classList.add('active');
                    const icon = button.querySelector('i');
                    if(icon) icon.className = 'fa-solid fa-chevron-up';
                }
            });
        });
    </script>
</main>

<?php include_once "views/layouts/footer.php"; ?>
