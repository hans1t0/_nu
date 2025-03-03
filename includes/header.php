<?php
require_once __DIR__ . '/../config.php';
$baseUrl = BASE_URL;  // Usa la constante definida en config.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $pagina === 'contacto' ? 'Contacta con los coordinadores de cada centro - Educap' : 'Servicios educativos educap.es'; ?>">
    <title><?php echo $titulo ?? SITE_NAME; ?></title>

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo CSS_URL; ?>styles.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link href="<?php echo IMAGES_URL; ?>favicon.png" rel="shortcut icon">

    <!-- Schema.org para la página de contacto -->
    <?php if ($pagina === 'contacto'): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?php echo SITE_NAME; ?>",
        "url": "<?php echo BASE_URL; ?>",
        "contactPoint": [
            {
                "@type": "ContactPoint",
                "telephone": "+34-647-729-651",
                "contactType": "customer service",
                "areaServed": "Playa San Juan",
                "availableLanguage": ["Spanish"]
            }
        ]
    }
    </script>
    <?php endif; ?>

    <style>
        .hero-section {
            position: relative;
            min-height: 80vh;
            background-size: cover;
            background-position: center;
            margin-top: -76px;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0.4));
        }
        .card {
            transition: all 0.3s ease;
            border: none;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .sticky-sidebar {
            position: sticky;
            top: 2rem;
        }
        .blur-bg {
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
