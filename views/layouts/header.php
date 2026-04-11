<?php require_once __DIR__ . "/../../config/config.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hotel Boutique Villa de Sant - Lujo, cultura y agilidad en el corazón de Quito.">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Hotel Boutique Villa de Sant'; ?></title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header-footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/components.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js" defer></script>
</head>
<body>
    <?php include_once __DIR__ . "/nav.php"; ?>
