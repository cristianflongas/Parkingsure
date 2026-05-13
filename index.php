<?php
/**
 * PARKINGSURE - Página Principal del Sistema
 * Landing page profesional con carrusel y sección de seguridad
 */

// Si el usuario ya está logueado, redirigir al dashboard
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkingSure - Sistema de Gestión de Parqueaderos</title>
    <meta name="description" content="Sistema profesional de gestión de parqueaderos con seguridad avanzada, control de accesos y facturación automática">
    <meta name="keywords" content="parqueadero, gestión, seguridad, parking, sistema, control">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="ParkingSure - Sistema de Gestión de Parqueaderos">
    <meta property="og:description" content="Sistema profesional con seguridad avanzada y control total">
    <meta property="og:type" content="website">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../../img/logo.png">
    
    <!-- CSS Principal -->
    <link href="../style/ps-core.css" rel="stylesheet">
    
    <style>
        /* Estilos adicionales para landing page */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: #fafafa;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero {
            height: 85vh;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: inset 0 0 50px rgba(0,0,0,0.05);
        }
        
        /* Carrusel */
        .carousel-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .carousel {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 2s ease-in-out;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .carousel-slide.active {
            opacity: 1;
        }
        
        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 2;
        }
        
        /* Contenido Principal */
        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            color: #000;
            padding: 1rem;
            max-width: 600px;
            animation: fadeInUp 1s ease-out;
        }
        
        .logo-hero {
            font-size: 3.2rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
            letter-spacing: -0.5px;
        }
        
        .logo-hero .logo-mark {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: #ffd700;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
            line-height: 50px;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.2);
        }
        
        .logo-hero .logo-text {
            display: inline-block;
            vertical-align: middle;
            color: var(--text-primary);
        }
        
        .tagline {
            font-size: 1.2rem;
            margin-bottom: 1.2rem;
            font-weight: 400;
            text-shadow: none;
            color: #2c3e50;
            letter-spacing: 0.5px;
        }
        
        .cta-button {
            display: inline-block;
            padding: 1rem 3rem;
            background: linear-gradient(135deg, #ffd700 0%, #ffcc00 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border: 2px solid transparent;
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.4);
            background: #ffcc00;
        }
        
        /* Elementos Decorativos */
        .hero-decoration {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #ffd700 10%, transparent 20%);
            z-index: 1;
        }
        
        .decoration-line {
            height: 100%;
            width: 1px;
            background: linear-gradient(90deg, transparent, #ffd700 50%, transparent);
            margin: 0 auto;
        }

        /* Sección de Contenido */
        .content-section {
            padding: 2.5rem 2rem;
            background: #fff;
            min-height: 100vh;
        }
        
        /* Sección de Seguridad */
        .security-section {
            padding: 2.5rem 2rem;
            background: #fff;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.6rem;
            color: #000;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1rem;
            color: #666;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #000;
        }
        
        .feature-description {
            color: #666;
            line-height: 1.6;
        }
        
        /* Sección de Beneficios */
        .benefits {
            background: #000;
            color: #fff;
            padding: 2rem 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .benefits::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
            pointer-events: none;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .benefit-item {
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .benefit-item::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .benefit-item:hover {
            transform: translateY(-10px) scale(1.05);
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--gold);
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.3);
        }
        
        .benefit-item:hover::before {
            opacity: 1;
        }
        
        .benefit-number {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            color: var(--gold);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .benefit-text {
            font-size: 1.1rem;
            opacity: 0.9;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .benefit-item:hover .benefit-number {
            transform: scale(1.1);
            text-shadow: 0 5px 15px rgba(255, 215, 0, 0.5);
        }
        
        .benefit-item:hover .benefit-text {
            opacity: 1;
            transform: translateY(-2px);
        }
        
        /* Footer */
        .footer {
            background: #000;
            color: #fff;
            padding: 1rem;
            text-align: center;
        }
        
        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .logo-hero {
                font-size: 2.5rem;
            }
            
            .logo-hero .logo-mark {
                width: 60px;
                height: 60px;
                line-height: 60px;
                font-size: 2rem;
            }
            
            .tagline {
                font-size: 1.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .benefits-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Indicadores del carrusel */
        .carousel-indicators {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 10px;
        }
        
        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .indicator.active {
            background: var(--gold);
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <!-- Hero Section Empresarial -->
    <section class="hero">
        <!-- Elemento decorativo superior -->
        <div class="hero-decoration">
            <div class="decoration-line"></div>
        </div>
        
        <!-- Contenido Principal -->
        <div class="hero-content">
            <div class="logo-hero">
                <span class="logo-mark">P</span>
                <span class="logo-text">PARKING<em>SURE</em></span>
            </div>
            <p class="tagline">Gestión Profesional y Segura de Parqueaderos</p>
            <a href="views/usuarios/login.php" class="cta-button">Iniciar Sesión</a>
        </div>
    </section>

    <!-- Sección de Contenido -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">🔐 Seguridad Avanzada</h2>
            <p class="section-subtitle">Protección total para tu parqueadero con tecnología de última generación</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-icon">🛡️</span>
                    <h3 class="feature-title">Encriptación de Datos</h3>
                    <p class="feature-description">Toda la información se encripta con los estándares más altos de seguridad, protegiendo datos de clientes y transacciones.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">👤</span>
                    <h3 class="feature-title">Control de Accesos</h3>
                    <p class="feature-description">Sistema de roles y permisos granular que garantiza que solo personal autorizado acceda a funciones críticas.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">📊</span>
                    <h3 class="feature-title">Auditoría Completa</h3>
                    <p class="feature-description">Registro detallado de todas las operaciones con trazabilidad total para cumplir con normativas de seguridad.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🔒</span>
                    <h3 class="feature-title">Sesiones Seguras</h3>
                    <p class="feature-description">Protección contra ataques de sesión con regeneración automática de tokens y timeouts configurables.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">💳</span>
                    <h3 class="feature-title">Pagos Seguros</h3>
                    <p class="feature-description">Procesamiento de pagos con validación múltiple y registro detallado de todas las transacciones.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🚨</span>
                    <h3 class="feature-title">Alertas en Tiempo Real</h3>
                    <p class="feature-description">Notificaciones instantáneas de actividades sospechosas y monitoreo continuo del sistema.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Beneficios -->
    <section class="benefits">
        <div class="container">
            <h2 class="section-title" style="color: white;">📈 ¿Por qué elegir ParkingSure?</h2>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-number">99.9%</div>
                    <div class="benefit-text">Uptime garantizado</div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">24/7</div>
                    <div class="benefit-text">Soporte técnico</div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">1000+</div>
                    <div class="benefit-text">Vehículos gestionados</div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">≤2s</div>
                    <div class="benefit-text">Tiempo de respuesta</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 ParkingSure - Sistema Profesional de Gestión de Parqueaderos</p>
            <p>Todos los derechos reservados | Seguridad y Confianza en cada Operación</p>
        </div>
    </footer>

    <script>
        // Carrusel automático
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;

        function changeSlide(index) {
            // Remover clase active de slide actual
            slides[currentSlide].classList.remove('active');
            indicators[currentSlide].classList.remove('active');
            
            // Actualizar slide actual
            currentSlide = index;
            
            // Agregar clase active al nuevo slide
            slides[currentSlide].classList.add('active');
            indicators[currentSlide].classList.add('active');
        }

        function nextSlide() {
            const nextIndex = (currentSlide + 1) % totalSlides;
            changeSlide(nextIndex);
        }

        // Carrusel automático cada 5 segundos
        setInterval(nextSlide, 5000);

        // Efecto parallax al hacer scroll
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        });

        // Animación de entrada para las tarjetas
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                }
            });
        }, observerOptions);

        // Observar todas las tarjetas de características
        document.querySelectorAll('.feature-card').forEach(card => {
            observer.observe(card);
        });

        // Efectos interactivos adicionales
        document.querySelectorAll('.benefit-item').forEach((item, index) => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.1) rotate(2deg)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1) rotate(0deg)';
            });
            
            // Animación de contador
            const number = this.querySelector('.benefit-number');
            if (number) {
                const finalValue = number.textContent;
                let currentValue = 0;
                const increment = parseFloat(finalValue) / 50;
                
                const countUp = () => {
                    if (currentValue < parseFloat(finalValue)) {
                        currentValue += increment;
                        number.textContent = currentValue.toFixed(1) + (finalValue.includes('%') ? '%' : '+');
                        setTimeout(countUp, 30);
                    } else {
                        number.textContent = finalValue;
                    }
                };
                
                // Iniciar animación cuando sea visible
                const itemObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !number.dataset.animated) {
                            number.dataset.animated = 'true';
                            countUp();
                        }
                    });
                }, { threshold: 0.5 });
                
                itemObserver.observe(item);
            }
        });

        // Efecto de onda en el logo
        const logoMark = document.querySelector('.logo-mark');
        if (logoMark) {
            logoMark.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.2) rotate(360deg)';
                this.style.background = 'linear-gradient(45deg, #ffcc00, #ffd700)';
            });
            
            logoMark.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1) rotate(0deg)';
                this.style.background = 'var(--gold)';
            });
        }

        // Efecto de escritura en el tagline
        const tagline = document.querySelector('.tagline');
        if (tagline) {
            const text = tagline.textContent;
            tagline.textContent = '';
            let charIndex = 0;
            
            const typeWriter = () => {
                if (charIndex < text.length) {
                    tagline.textContent += text.charAt(charIndex);
                    charIndex++;
                    setTimeout(typeWriter, 100);
                }
            };
            
            // Iniciar animación cuando sea visible
            const taglineObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !tagline.dataset.animated) {
                        tagline.dataset.animated = 'true';
                        typeWriter();
                    }
                });
            }, { threshold: 0.5 });
            
            taglineObserver.observe(tagline);
        }
    </script>
</body>
</html>