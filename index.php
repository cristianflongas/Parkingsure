<?php
/**
 * PARKINGSURE - Página Principal del Sistema
 * Landing page profesional con carrusel y sección de seguridad
 */

// La sesión se inicia para verificar estado, pero no se redirige automáticamente 
// para permitir al usuario ver la página principal (Index) incluso si está logueado.
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkingSure - Sistema de Gestión de Parqueaderos</title>
    <meta name="description" content="Sistema profesional de gestión de parqueaderos con seguridad avanzada, control de accesos y facturación automática">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="img/logo.png">
    
    <style>
        :root {
            --primary-yellow: #ffd700;
            --dark-black: #0a0a0a;
            --text-black: #1a1a1a;
            --bg-white: #ffffff;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-black);
            background-color: var(--bg-white);
        }

        /* Navbar Formal */
        .navbar-formal {
            background-color: var(--bg-white);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }

        .navbar-formal .navbar-brand {
            font-weight: 900;
            font-size: 1.8rem;
            color: var(--dark-black);
            letter-spacing: -0.5px;
        }

        .navbar-formal .logo-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary-yellow);
            border-radius: 50%;
            margin-right: 10px;
            font-size: 1.5rem;
            color: var(--dark-black);
            font-weight: 900;
        }

        /* Carousel Section */
        .carousel-item {
            height: 85vh;
            min-height: 500px;
        }
        
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }

        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65); /* Fondo oscuro para resaltar el texto y dar un toque más sobrio */
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            color: white;
            z-index: 10;
            pointer-events: none;
        }

        .carousel-overlay * {
            pointer-events: auto;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-title span {
            color: var(--primary-yellow);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 2.5rem;
            max-width: 800px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .btn-custom {
            padding: 1rem 3rem;
            background: var(--primary-yellow);
            color: var(--dark-black);
            font-weight: 800;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-yellow);
            text-decoration: none;
            display: inline-block;
        }

        .btn-custom:hover {
            background: transparent;
            color: var(--primary-yellow);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }

        /* Secciones de Contenido */
        .section-title {
            font-weight: 900;
            font-size: 2.5rem;
            text-transform: uppercase;
            margin-bottom: 3rem;
            position: relative;
            display: inline-block;
            color: var(--dark-black);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--primary-yellow);
        }

        .feature-card {
            padding: 2.5rem;
            background: var(--bg-white);
            border: 1px solid #eaeaea;
            border-radius: 8px;
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
        }

        .feature-card:hover {
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            border-color: var(--primary-yellow);
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--dark-black);
            margin-bottom: 1.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: var(--bg-light);
            border-radius: 50%;
            border: 2px solid var(--primary-yellow);
        }

        /* Stats Section */
        .stats-section {
            background: var(--dark-black);
            color: var(--bg-white);
            padding: 5rem 0;
            position: relative;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 5px;
            background: var(--primary-yellow);
        }

        .stat-item h3 {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--primary-yellow);
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            margin: 0;
        }

        /* Footer */
        footer {
            background: #050505;
            color: var(--bg-white);
            padding: 2rem 0;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1.2rem;
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Formal -->
    <nav class="navbar navbar-formal sticky-top">
        <div class="container">
            <a class="navbar-brand m-0" href="#">
                <span class="logo-mark">P</span>
                PARKING<span style="color: #666; font-weight: 400;">SURE</span>
            </a>
            <a href="views/usuarios/login.php" class="btn-custom" style="padding: 0.5rem 1.5rem; font-size: 0.9rem;">Iniciar Sesión</a>
        </div>
    </nav>

    <!-- Carrusel Bootstrap -->
    <div id="carouselExampleControls" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <!-- Overlay del Hero (Fijo sobre el carrusel) -->
        <div class="carousel-overlay">
            <h1 class="hero-title">Gestión <span>Profesional</span></h1>
            <p class="hero-subtitle">Sistema seguro de administración de parqueaderos con control de accesos, facturación y monitoreo avanzado.</p>
            <a href="views/usuarios/login.php" class="btn-custom">Acceder al Sistema</a>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="5000">
                <img src="img/slider1.jpg" class="d-block w-100" alt="Instalaciones 1">
            </div>
            <div class="carousel-item" data-bs-interval="5000">
                <img src="img/slider2.jpg" class="d-block w-100" alt="Instalaciones 2">
            </div>
            <div class="carousel-item" data-bs-interval="5000">
                <img src="img/slider3.jpg" class="d-block w-100" alt="Instalaciones 3">
            </div>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev" style="z-index: 20;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next" style="z-index: 20;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Sección de Seguridad -->
    <section class="py-5 bg-white">
        <div class="container py-5 text-center">
            <h2 class="section-title">Seguridad y Confianza</h2>
            <p class="mb-5 text-muted mx-auto" style="max-width: 700px; font-size: 1.1rem;">
                Protección total para tu parqueadero con tecnología de vanguardia. Nuestra plataforma garantiza la integridad de tus datos y agiliza todas las operaciones.
            </p>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h4 class="fw-bold mb-3">Encriptación de Datos</h4>
                        <p class="text-muted mb-0">Toda la información se encripta con los estándares más altos, protegiendo datos de clientes y transacciones de forma segura.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">👤</div>
                        <h4 class="fw-bold mb-3">Control de Accesos</h4>
                        <p class="text-muted mb-0">Sistema de roles y permisos granular que garantiza que solo personal autorizado acceda a las funciones críticas.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h4 class="fw-bold mb-3">Auditoría Completa</h4>
                        <p class="text-muted mb-0">Registro detallado de todas las operaciones con trazabilidad total para cumplir con normativas de seguridad vigentes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Estadísticas/Beneficios -->
    <section class="stats-section text-center">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-6 stat-item">
                    <h3 class="counter">99.9%</h3>
                    <p>Uptime Garantizado</p>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <h3 class="counter">24/7</h3>
                    <p>Soporte Técnico</p>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <h3 class="counter">1000+</h3>
                    <p>Vehículos Gestionados</p>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <h3 class="counter">≤2s</h3>
                    <p>Tiempo de Respuesta</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> ParkingSure - Sistema Profesional de Gestión de Parqueaderos</p>
            <p class="mb-0 text-muted" style="font-size: 0.9rem;">Todos los derechos reservados | Seguridad y Confianza en cada Operación</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>