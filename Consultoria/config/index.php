<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/correo.php";

$mensaje = "";
$tipoMensaje = "";
$nombre = $correo = $telefono = $empresa = $peticion = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $empresa = trim($_POST["empresa"] ?? "");
    $peticion = trim($_POST["peticion"] ?? "");

    if (empty($nombre) || empty($correo) || empty($peticion)) {
        $mensaje = "Los campos Nombre, Correo y Petición son obligatorios.";
        $tipoMensaje = "error";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El correo electrónico no es válido.";
        $tipoMensaje = "error";
    } else {
        try {
            $pdo = obtenerConexion();
            
            $sql = "INSERT INTO solicitudes_contacto 
                    (nombre, correo, telefono, empresa, mensaje, estado, fecha_creacion) 
                    VALUES (:nombre, :correo, :telefono, :empresa, :mensaje, 'NUEVO', NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":nombre" => $nombre,
                ":correo" => $correo,
                ":telefono" => $telefono,
                ":empresa" => $empresa,
                ":mensaje" => $peticion
            ]);

            $solicitudId = $pdo->lastInsertId();
            $correoEnviado = enviarCorreoConfirmacion($correo, $nombre);

            if ($correoEnviado) {
                $mensaje = "¡Solicitud enviada con éxito!<br>Hemos enviado un correo de confirmación.";
                $tipoMensaje = "exito";
                $nombre = $correo = $telefono = $empresa = $peticion = "";
            } else {
                $mensaje = "Solicitud guardada, pero hubo un error al enviar el correo.";
                $tipoMensaje = "error";
            }

        } catch (PDOException $e) {
            $mensaje = "Error al guardar en la base de datos: " . $e->getMessage();
            $tipoMensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultoría Web - Desarrollo Profesional</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        /* Header */
        .main-header {
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .main-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }

        .logo {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .logo h1 {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 10px;
            background: linear-gradient(to right, #fff, #bdc3c7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .logo .tagline {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
            letter-spacing: 2px;
        }

        .admin-access {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 2;
        }

        .admin-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .admin-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Navigation */
        .main-nav {
            background: var(--light);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            padding: 0 20px;
        }

        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link:hover {
            background: var(--secondary);
            color: white;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 80%;
        }

        /* Main Content */
        .main-content {
            padding: 40px;
        }

        .section {
            margin-bottom: 60px;
            padding: 40px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-left: 5px solid var(--secondary);
            transition: transform 0.3s;
        }

        .section:hover {
            transform: translateY(-5px);
        }

        .section-title {
            color: var(--primary);
            font-size: 2.2rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--accent);
            position: relative;
            display: inline-block;
        }

        .section-title::before {
            content: "";
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--secondary);
        }

        .section-subtitle {
            color: var(--secondary);
            font-size: 1.4rem;
            margin: 25px 0 15px;
            font-weight: 600;
        }

        /* About Section */
        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .about-card {
            background: linear-gradient(145deg, #f8f9fa, #ffffff);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.1);
            border: 1px solid rgba(52, 152, 219, 0.1);
        }

        .icon-container {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: white;
            font-size: 1.8rem;
        }

        /* Contact Info - CORREGIDO */
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 25px;
            background: var(--light);
            border-radius: 12px;
            transition: all 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .contact-item:hover {
            background: var(--secondary);
            color: white;
            transform: translateX(10px);
            border-color: var(--secondary);
        }

        .contact-icon {
            font-size: 2rem;
            color: var(--secondary);
            min-width: 60px;
            text-align: center;
        }

        .contact-item:hover .contact-icon {
            color: white;
        }

        .contact-details {
            flex: 1;
        }

        .contact-details h3 {
            margin-bottom: 10px;
            color: var(--primary);
            font-size: 1.3rem;
        }

        .contact-item:hover .contact-details h3 {
            color: white;
        }

        .contact-details p {
            margin-bottom: 5px;
            line-height: 1.5;
        }

        .phone-numbers {
            margin-top: 10px;
        }

        .phone-number {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        /* Technologies */
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .tech-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px 15px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
        }

        .tech-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
        }

        .tech-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2));
        }

        .tech-name {
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        /* Pricing Plans - MODIFICADO */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .plan-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 2px solid transparent;
            position: relative;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            border-color: var(--secondary);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .plan-header {
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .plan-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .plan-price {
            font-size: 3rem;
            font-weight: 800;
            margin: 20px 0;
        }

        .plan-price span {
            font-size: 1.2rem;
            font-weight: 400;
        }

        .plan-features {
            padding: 30px;
            list-style: none;
        }

        .plan-features li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            color: var(--success);
            font-size: 1.1rem;
        }

        .plan-features i.fa-times {
            color: #e74c3c;
        }

        .plan-waiting {
            background: rgba(243, 156, 18, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 0.9rem;
            border-left: 4px solid var(--warning);
        }

        .plan-button {
            display: block;
            background: var(--secondary);
            color: white;
            text-align: center;
            padding: 18px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            border: none;
            width: 100%;
            cursor: pointer;
            font-family: inherit;
        }

        .plan-button:hover {
            background: var(--primary);
            letter-spacing: 1px;
        }

        /* Form Section */
        .form-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px;
            border-radius: 20px;
            margin-top: 60px;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: white;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            background: rgba(255,255,255,0.9);
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: white;
            background: white;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }

        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: white;
            color: var(--primary);
            border: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px auto 0;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .submit-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .mensaje {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .exito {
            background: rgba(39, 174, 96, 0.2);
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 2px solid #e74c3c;
        }

        /* Footer */
        .main-footer {
            background: var(--primary);
            color: white;
            padding: 40px;
            text-align: center;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .footer-link {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-link:hover {
            color: white;
            text-decoration: underline;
        }

        .copyright {
            margin-top: 30px;
            color: #95a5a6;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-header {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .nav-container {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .section {
                padding: 25px;
            }
            
            .contact-info {
                grid-template-columns: 1fr;
            }
            
            .pricing-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section {
            animation: fadeIn 0.6s ease-out;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Header -->
        <header class="main-header">
            <div class="admin-access">
                <a href="login.php" class="admin-btn">
                    <i class="fas fa-user-shield"></i>
                    Panel Admin
                </a>
            </div>
            
            <div class="logo">
                <h1>Consultoría Web</h1>
                <div class="tagline">Soluciones Digitales que Transforman Negocios</div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="main-nav">
            <div class="nav-container">
                <a href="#conocemos" class="nav-link">¿Qué Hacemos?</a>
                <a href="#nosotros" class="nav-link">Sobre Nosotros</a>
                <a href="#mision" class="nav-link">Misión y Visión</a>
                <a href="#tecnologias" class="nav-link">Tecnologías</a>
                <a href="#planes" class="nav-link">Planes Mensuales</a>
                <a href="#contacto" class="nav-link">Solicitar Servicio</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Sección 1: Conócenos -->
            <section id="conocemos" class="section">
                <h2 class="section-title">¿Qué Hacemos?</h2>
                <div class="about-grid">
                    <div class="about-card">
                        <div class="icon-container">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3 class="section-subtitle">Desarrollo Web Personalizado</h3>
                        <p>Creamos sitios web a medida que se adaptan perfectamente a las necesidades de tu negocio. Desde landing pages hasta plataformas complejas.</p>
                    </div>
                    
                    <div class="about-card">
                        <div class="icon-container">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="section-subtitle">E-commerce y Tiendas Online</h3>
                        <p>Implementamos soluciones de comercio electrónico con pasarelas de pago seguras, gestión de inventario y experiencia de compra optimizada.</p>
                    </div>
                    
                    <div class="about-card">
                        <div class="icon-container">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="section-subtitle">Aplicaciones Móviles</h3>
                        <p>Desarrollamos apps nativas e híbridas para iOS y Android que conectan tu negocio con los clientes en cualquier momento y lugar.</p>
                    </div>
                    
                    <div class="about-card">
                        <div class="icon-container">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="section-subtitle">Consultoría Digital</h3>
                        <p>Asesoramos en estrategias digitales, SEO, marketing online y transformación digital para maximizar tu presencia en internet.</p>
                    </div>
                </div>
            </section>

            <!-- Sección 2: Sobre Nosotros - MODIFICADO -->
            <section id="nosotros" class="section">
                <h2 class="section-title">Sobre Nosotros</h2>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Teléfonos de Contacto</h3>
                            <div class="phone-numbers">
                                <span class="phone-number">📱 55 3780 3187</span>
                                <span class="phone-number">📱 55 2968 9937</span>
                            </div>
                            <p>Lunes a Viernes: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Correo Electrónico</h3>
                            <p>consultoriaelgit@gmail.com</p>
                            <p style="margin-top: 10px; font-size: 0.9rem; opacity: 0.9;">
                                Respondemos en menos de 24 horas
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Nuestra Dirección</h3>
                            <p>5 de Mayo S/N, Tepanquiahuac</p>
                            <p>54783 Teoloyucan, Estado de México</p>
                            <p style="margin-top: 10px; font-size: 0.9rem; opacity: 0.9;">
                                <i class="fas fa-clock"></i> Atención con cita previa
                            </p>
                        </div>
                    </div>
                </div>

                <h3 class="section-subtitle" style="margin-top: 40px;">Nuestro Trabajo</h3>
                <p>Hemos desarrollado más de 150 proyectos exitosos, incluyendo:</p>
                
                <div style="margin-top: 20px; padding: 20px; background: var(--light); border-radius: 10px;">
                    <h4 style="color: var(--secondary); margin-bottom: 15px;">
                        <i class="fas fa-briefcase"></i> Proyectos Destacados
                    </h4>
                    <ul style="columns: 2; list-style-type: none;">
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--success);"></i> Sistema de Gestión Educativa</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--success);"></i> Plataforma E-learning</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--success);"></i> App de Delivery</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--success);"></i> CRM Empresarial</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--success);"></i> Portal Gubernamental</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--success);"></i> Red Social Especializada</li>
                    </ul>
                </div>
            </section>

            <!-- Sección 3: Misión y Visión -->
            <section id="mision" class="section">
                <h2 class="section-title">Misión y Visión</h2>
                
                <div class="about-grid">
                    <div class="about-card">
                        <div class="icon-container" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="section-subtitle">Nuestra Misión</h3>
                        <p>Proporcionar soluciones tecnológicas innovadoras y de calidad que impulsen el crecimiento de nuestros clientes, superando sus expectativas con profesionalismo, compromiso y atención personalizada en cada proyecto.</p>
                    </div>
                    
                    <div class="about-card">
                        <div class="icon-container" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="section-subtitle">Nuestra Visión</h3>
                        <p>Ser la consultoría web de referencia en Latinoamérica, reconocida por nuestra excelencia técnica, innovación constante y capacidad para transformar ideas en realidades digitales exitosas y sostenibles.</p>
                    </div>
                </div>

                <h3 class="section-subtitle" style="margin-top: 40px;">Nuestros Valores</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <i class="fas fa-handshake" style="font-size: 2rem; color: var(--secondary); margin-bottom: 15px;"></i>
                        <h4>Compromiso</h4>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <i class="fas fa-lightbulb" style="font-size: 2rem; color: var(--warning); margin-bottom: 15px;"></i>
                        <h4>Innovación</h4>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <i class="fas fa-users" style="font-size: 2rem; color: var(--success); margin-bottom: 15px;"></i>
                        <h4>Trabajo en Equipo</h4>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <i class="fas fa-star" style="font-size: 2rem; color: var(--accent); margin-bottom: 15px;"></i>
                        <h4>Calidad</h4>
                    </div>
                </div>
            </section>

            <!-- Sección 4: Tecnologías -->
            <section id="tecnologias" class="section">
                <h2 class="section-title">Tecnologías que Dominamos</h2>
                <p style="margin-bottom: 30px; font-size: 1.1rem;">Utilizamos las mejores tecnologías del mercado para garantizar resultados óptimos:</p>
                
                <div class="tech-grid">
                    <div class="tech-card">
                        <div class="tech-icon">PHP</div>
                        <div class="tech-name">PHP 8+</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">HTML5</div>
                        <div class="tech-name">HTML5</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">CSS3</div>
                        <div class="tech-name">CSS3</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">JS</div>
                        <div class="tech-name">JavaScript</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">⚛️</div>
                        <div class="tech-name">React.js</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">Laravel</div>
                        <div class="tech-name">Laravel</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">Node.js</div>
                        <div class="tech-name">Node.js</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">Spring</div>
                        <div class="tech-name">Spring Boot</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">TS</div>
                        <div class="tech-name">TypeScript</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">MySQL</div>
                        <div class="tech-name">MySQL</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">MongoDB</div>
                        <div class="tech-name">MongoDB</div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">AWS</div>
                        <div class="tech-name">AWS</div>
                    </div>
                </div>
            </section>

            <!-- Sección 5: Planes Mensuales - MODIFICADO -->
            <section id="planes" class="section">
                <h2 class="section-title">Planes de Desarrollo</h2>
                <p style="margin-bottom: 30px; font-size: 1.1rem;">Elige el plan que mejor se adapte a tus necesidades y presupuesto:</p>
                
                <div class="pricing-grid">
                    <!-- Plan Gratuito -->
                    <div class="plan-card">
                        <div class="plan-header" style="background: linear-gradient(135deg, #95a5a6, #7f8c8d);">
                            <h3 class="plan-name">Plan Gratuito</h3>
                            <div class="plan-price">$0<span>/mes</span></div>
                            <p>Para proyectos pequeños</p>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Sitio Web Básico (3 páginas)</li>
                            <li><i class="fas fa-check"></i> Diseño Responsivo Básico</li>
                            <li><i class="fas fa-check"></i> Formulario de Contacto Simple</li>
                            <li><i class="fas fa-check"></i> Hosting Básico</li>
                            <li><i class="fas fa-times"></i> SEO Básico</li>
                            <li><i class="fas fa-times"></i> Soporte Prioritario</li>
                            <li><i class="fas fa-times"></i> Certificado SSL</li>
                            <li><i class="fas fa-times"></i> Panel Administrativo</li>
                        </ul>
                        <div class="plan-waiting">
                            <strong><i class="fas fa-clock"></i> Tiempo de Espera:</strong><br>
                            Lista de espera indefinida
                        </div>
                        <button type="button" class="plan-button" onclick="scrollToForm()">Solicitar</button>
                    </div>
                    
                    <!-- Plan Estándar -->
                    <div class="plan-card" style="transform: scale(1.05); border-color: var(--secondary);">
                        <div class="plan-header" style="background: linear-gradient(135deg, var(--secondary), #2980b9);">
                            <h3 class="plan-name">Plan Estándar</h3>
                            <div class="plan-price">$200<span>/mes</span></div>
                            <p>Perfecto para PYMES</p>
                            <div style="background: var(--accent); color: white; padding: 5px 15px; border-radius: 20px; margin-top: 10px; font-size: 0.9rem;">
                                RECOMENDADO
                            </div>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Todo del Plan Gratuito</li>
                            <li><i class="fas fa-check"></i> Hasta 8 Páginas</li>
                            <li><i class="fas fa-check"></i> Diseño Personalizado</li>
                            <li><i class="fas fa-check"></i> SEO Básico Incluido</li>
                            <li><i class="fas fa-check"></i> Certificado SSL Gratis</li>
                            <li><i class="fas fa-check"></i> 5 Horas de Soporte Mensual</li>
                            <li><i class="fas fa-check"></i> Panel Administrativo Básico</li>
                            <li><i class="fas fa-times"></i> E-commerce</li>
                        </ul>
                        <div class="plan-waiting">
                            <strong><i class="fas fa-clock"></i> Tiempo de Entrega:</strong><br>
                            2 a 3 semanas después del pago
                        </div>
                        <button type="button" class="plan-button" style="background: var(--secondary);" onclick="scrollToForm()">Solicitar</button>
                    </div>
                    
                    <!-- Plan Premium -->
                    <div class="plan-card">
                        <div class="plan-header" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                            <h3 class="plan-name">Plan Premium</h3>
                            <div class="plan-price">$500<span>/mes</span></div>
                            <p>Solución Completa e Inmediata</p>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Todo del Plan Estándar</li>
                            <li><i class="fas fa-check"></i> Páginas Ilimitadas</li>
                            <li><i class="fas fa-check"></i> Diseño Premium Personalizado</li>
                            <li><i class="fas fa-check"></i> SEO Avanzado</li>
                            <li><i class="fas fa-check"></i> 15 Horas de Soporte Mensual</li>
                            <li><i class="fas fa-check"></i> Tienda Online Básica</li>
                            <li><i class="fas fa-check"></i> Panel Administrativo Avanzado</li>
                            <li><i class="fas fa-check"></i> Integración con Redes Sociales</li>
                        </ul>
                        <div class="plan-waiting">
                            <strong><i class="fas fa-bolt"></i> Tiempo de Entrega:</strong><br>
                            <span style="color: var(--success); font-weight: bold;">INICIO INMEDIATO</span> después del pago
                        </div>
                        <button type="button" class="plan-button" style="background: #e74c3c;" onclick="scrollToForm()">Solicitar</button>
                    </div>
                </div>
                
                <div style="margin-top: 40px; padding: 25px; background: #f8f9fa; border-radius: 12px; border-left: 4px solid var(--success);">
                    <h3 style="color: var(--primary); margin-bottom: 15px;">
                        <i class="fas fa-info-circle"></i> Información Importante
                    </h3>
                    <ul style="list-style-type: none; padding-left: 0;">
                        <li style="margin-bottom: 10px; padding-left: 25px; position: relative;">
                            <i class="fas fa-check" style="color: var(--success); position: absolute; left: 0;"></i>
                            Todos los planes incluyen dominio personalizado (.com, .mx, etc.)
                        </li>
                        <li style="margin-bottom: 10px; padding-left: 25px; position: relative;">
                            <i class="fas fa-check" style="color: var(--success); position: absolute; left: 0;"></i>
                            Facturación disponible para todos los planes
                        </li>
                        <li style="margin-bottom: 10px; padding-left: 25px; position: relative;">
                            <i class="fas fa-check" style="color: var(--success); position: absolute; left: 0;"></i>
                            Contrato de servicio por 12 meses mínimo
                        </li>
                        <li style="padding-left: 25px; position: relative;">
                            <i class="fas fa-check" style="color: var(--success); position: absolute; left: 0;"></i>
                            Garantía de satisfacción de 30 días
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Sección 6: Formulario de Contacto -->
            <section id="contacto" class="form-section">
                <div class="form-container">
                    <h2 class="section-title" style="color: white;">Solicita tu Página Web</h2>
                    <p style="margin-bottom: 30px; text-align: center; font-size: 1.1rem;">
                        Completa el formulario y nos pondremos en contacto contigo en menos de 24 horas.
                    </p>

                    <?php if (!empty($mensaje)): ?>
                        <div class="mensaje <?php echo $tipoMensaje; ?>">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($nombre); ?>" 
                                       class="form-input" required placeholder="Tu nombre completo">
                            </div>

                            <div class="form-group">
                                <label for="correo" class="form-label">Correo Electrónico *</label>
                                <input type="email" id="correo" name="correo" 
                                       value="<?php echo htmlspecialchars($correo); ?>" 
                                       class="form-input" required placeholder="ejemplo@empresa.com">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono" class="form-label">Teléfono *</label>
                                <input type="tel" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($telefono); ?>" 
                                       class="form-input" required placeholder="55 1234 5678"
                                       pattern="[0-9]{10}"
                                       title="Ingresa 10 dígitos sin espacios ni guiones">
                                <small style="display: block; margin-top: 5px; font-size: 0.85rem; opacity: 0.8;">
                                    Ej: 5537803187 o 5529689937 (10 dígitos)
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="empresa" class="form-label">Empresa / Organización</label>
                                <input type="text" id="empresa" name="empresa" 
                                       value="<?php echo htmlspecialchars($empresa); ?>" 
                                       class="form-input" placeholder="Nombre de tu empresa">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="plan" class="form-label">Plan de Interés *</label>
                            <select id="plan" name="plan" class="form-input" required>
                                <option value="">Selecciona un plan...</option>
                                <option value="gratuito">Plan Gratuito (Espera indefinida)</option>
                                <option value="estandar">Plan Estándar - $200/mes (2-3 semanas)</option>
                                <option value="premium">Plan Premium - $500/mes (Inicio inmediato)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="peticion" class="form-label">¿Qué necesitas? *</label>
                            <textarea id="peticion" name="peticion" 
                                      class="form-textarea" required 
                                      placeholder="Describe tu proyecto, necesidades específicas, preferencias de tecnología, presupuesto aproximado, y cualquier otro detalle importante..."><?php echo htmlspecialchars($peticion); ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-rocket"></i>
                            Enviar Solicitud
                        </button>
                    </form>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="footer-content">
                <h3 style="margin-bottom: 20px;">Consultoría Web</h3>
                <p>Transformando ideas en realidades digitales desde 2020</p>
                
                <div class="contact-info" style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <div style="text-align: left;">
                        <h4 style="color: white; margin-bottom: 10px;">Contacto</h4>
                        <p><i class="fas fa-phone"></i> 55 3780 3187 / 55 2968 9937</p>
                        <p><i class="fas fa-envelope"></i> consultoriaelgit@gmail.com</p>
                        <p><i class="fas fa-map-marker-alt"></i> Teoloyucan, Estado de México</p>
                    </div>
                </div>
                
                <div class="footer-links">
                    <a href="#conocemos" class="footer-link">Servicios</a>
                    <a href="#nosotros" class="footer-link">Nosotros</a>
                    <a href="#tecnologias" class="footer-link">Tecnologías</a>
                    <a href="#planes" class="footer-link">Planes</a>
                    <a href="login.php" class="footer-link">Admin</a>
                </div>
                
                <div class="copyright">
                    &copy; 2024 Consultoría Web. Todos los derechos reservados.<br>
                    <small style="opacity: 0.8;">5 de Mayo S/N, Tepanquiahuac, Teoloyucan, Méx.</small>
                </div>
            </div>
        </footer>
    </div>

    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <script>
        // Función para hacer scroll al formulario
        function scrollToForm() {
            const formSection = document.getElementById('contacto');
            formSection.scrollIntoView({ behavior: 'smooth' });
            
            // Auto-enfoque en el primer campo después de 500ms
            setTimeout(() => {
                document.getElementById('nombre').focus();
            }, 500);
        }

        // Smooth scrolling para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Validación de teléfono - CORREGIDO
        document.getElementById('telefono').addEventListener('input', function(e) {
            // Solo permitir números y limitar a 10 dígitos
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            
            e.target.value = value;
            
            // Formatear para mostrar
            if (value.length === 10) {
                e.target.style.borderColor = '#27ae60';
            } else {
                e.target.style.borderColor = '';
            }
        });

        // Animación al hacer scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observar todas las secciones
        document.querySelectorAll('.section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(section);
        });

        // Nav activo al hacer scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });

        // Auto-enfoque en el primer campo al cargar
        document.getElementById('nombre').focus();
        
        // Mejorar experiencia del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const telefono = document.getElementById('telefono').value;
            if (telefono.length !== 10) {
                e.preventDefault();
                alert('Por favor, ingresa un número de teléfono válido de 10 dígitos.');
                document.getElementById('telefono').focus();
                return false;
            }
        });
    </script>
</body>
</html>