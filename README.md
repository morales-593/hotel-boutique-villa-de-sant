# Hotel Boutique Villa de Sant

Sistema de gestión y reservas para el Hotel Boutique Villa de Sant. Un sistema integral diseñado para ofrecer una experiencia premium tanto a los huéspedes como al personal administrativo.

## 📋 Descripción del Sistema

Este sistema es una plataforma web completa que automatiza los procesos centrales del hotel. Facilita la interacción entre los clientes y la administración, proporcionando herramientas modernas para la reserva de habitaciones y la gestión operativa.

### Características Principales:

- **Interfaz de Usuario (Front-end):**
  - **Página de Inicio:** Diseño premium con efectos de desplazamiento (scroll-anim) y collage de fotos dinámico.
  - **Catálogo de Habitaciones:** Visualización detallada por categorías y servicios.
  - **Motor de Reservas:** Formulario intuitivo sincronizado con la disponibilidad real.
  - **Experiencias & Galería:** Promoción visual de actividades exclusivas con efecto de vidrio (glassmorphism).

- **Panel de Administración Modernizado (Back-end):**
  - **Dashboard Fluido:** Resumen de actividad en disposición de 3 columnas (Habitaciones, Disponibles, Reservas) con marca de agua corporativa.
  - **Gestión de Usuarios (RBAC):** Sistema de control de acceso por roles (Admin vs Staff). Solo el administrador gestiona accesos.
  - **Control de Reservas Avanzado:**
    - Perfeccionamiento de la búsqueda y filtros por fecha.
    - **Reserva Manual:** Modal responsivo para registros directos por categoría.
    - **Auto-compactación de IDs:** Al eliminar registros, el sistema reordena automáticamente los IDs para mantener la base de datos limpia.
  - **Sistema de Cupones Inteligente:** Lógica de expiración automática por fecha, control de estado bajo demanda y edición dinámica de códigos de descuento.
    - **Gestión de Precios e Información:** Interfaz interactiva para actualizar tarifas, descripciones y fotos de portada de la vista de cada categoría al instante.
  - **Optimización UI/UX:** Tipografía técnica (0.9rem), Sidebar compacto (240px) y modales responsivos con scroll interno para dispositivos móviles.

## 🛠️ Tecnologías Utilizadas

- **Servidor:** Apache con PHP 8.x
- **Base de Datos:** MySQL (Motor InnoDB)
- **Arquitectura:** Patrón MVC (Modelo-Vista-Controlador) simplificado para mayor agilidad.
- **Seguridad:** 
  - Conexión PDO con Sentencias Preparadas.
  - Encriptación de contraseñas mediante BCRYPT.
  - Manejo de sesiones securizado.
- **Diseño:** 
  - CSS3 Personalizado (Vanilla CSS).
  - Frameworks ligeros para interactividad (SweetAlert2, FontAwesome).

## 📂 Estructura del Directorio

```text
/
├── api/            # Lógica de procesamiento para llamadas AJAX
├── assets/         # CSS, JS, Imágenes y Fuentes
├── config/         # Configuraciones globales, base de datos y sesiones
├── controllers/    # Controladores que manejan el flujo de la aplicación
├── models/         # Modelos de datos para interactuar con la DB
├── views/          # Archivos de vista (públicos y administrativos)
├── database.sql    # Estructura e información inicial de la base de datos
├── index.php       # Enrutador principal y punto de entrada
└── README.md       # Documentación del sistema
```

## 🚀 Instalación

1. **Preparar el entorno:** Asegúrate de tener XAMPP o Laragon instalado con soporte para PHP 8.
2. **Ubicación:** Copia la carpeta del proyecto en el directorio `htdocs` (o equivalente).
3. **Base de Datos:**
   - Crea una base de datos llamada `hotel_villa_de_sant`.
   - Importa el archivo `database.sql`.
4. **Configuración:** Edita `config/database.php` con tus credenciales locales si es necesario.
5. **Acceso:** Abre tu navegador y dirígete a `http://localhost/hotel-boutique-villa-de-sant`.

## 🛡️ Medidas de Seguridad Aplicadas

Para garantizar la integridad de los datos y la estabilidad del sistema, se han implementado las siguientes medidas:

1. **Prevención de Inyección SQL:** Todas las consultas a la base de datos utilizan `PDO::prepare()` para neutralizar ataques.
2. **Protección de Archivos Sensibles:** Configuración de `.htaccess` para bloquear el acceso directo a carpetas de lógica (`config/`, `models/`, `controllers/`).
3. **Manejo Blindado de Sesiones:** Atributos `HttpOnly` en cookies para mitigar riesgos de XSS y secuestro de sesión.
4. **Validación de Entradas:** Filtrado de datos recibidos del lado del cliente antes de su procesamiento.
5. **Desactivación de Listado de Directorio:** Evita que atacantes puedan navegar por la estructura de archivos en el navegador.

## 📝 Resumen Técnico de Implementación (Prompt de Estado)

Este proyecto representa un sistema de gestión hotelera de grado comercial con las siguientes capacidades activas:

- **Estatus del Motor de Reservas:** 
  - Gestión dinámica de categorías (Single, Queen, Suite, etc.).
  - Validación de cupones en tiempo real con lógica de expiración.
  - Sincronización automática de disponibilidad tras la reserva.
- **Pasarela de Pagos (Actualizado):**
  - Integración con **Mercado Pago Checkout Pro** configurada en **Dólares (USD)**.
  - Optimizada para tarjetas de crédito (**Visa**, **MasterCard**).
  - Flujo de datos pre-llenados (Nombre, Email, Teléfono) para reducir la fricción del usuario.
  - Sistema de **Webhooks** para confirmación automática de reservas en segundo plano.
  - Página de éxito personalizada con diseño corporativo premium.
- **Panel Administrativo:**
  - CRUD completo de reservaciones con filtros avanzados.
  - Sistema de roles (Admin/Staff) con control de acceso granular.
  - Edición de contenidos (imágenes, precios, características) sin tocar el código.
- **Infraestructura:**
  - Código desacoplado en una arquitectura MVC simplificada.
  - Rutas amigables mediante `.htaccess`.
  - Diseño responsivo "mobile-first" con Vanilla CSS y animaciones avanzadas.

---
© 2026 Hotel Boutique Villa de Sant. Todos los derechos reservados.
