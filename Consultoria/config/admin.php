<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Verificar autenticación y rol de admin
verificarAutenticacion();
verificarRol('admin');

$usuario = obtenerUsuario();

// Obtener estadísticas
try {
    $pdo = obtenerConexion();
    
    // Total de solicitudes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM solicitudes_contacto");
    $totalSolicitudes = $stmt->fetch()['total'];
    
    // Solicitudes nuevas
    $stmt = $pdo->query("SELECT COUNT(*) as nuevas FROM solicitudes_contacto WHERE estado = 'NUEVO'");
    $solicitudesNuevas = $stmt->fetch()['nuevas'];
    
    // Total de usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $totalUsuarios = $stmt->fetch()['total'];
    
    // Últimas 5 solicitudes
    $stmt = $pdo->query("SELECT * FROM solicitudes_contacto ORDER BY fecha_creacion DESC LIMIT 5");
    $ultimasSolicitudes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $errorBD = "Error al cargar estadísticas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Consultoría Web</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: white;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Container */
        .container {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 20px 0;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            padding: 15px 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: #f8f9fa;
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .nav-icon {
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .welcome-section h2 {
            color: var(--dark);
            margin-bottom: 10px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary {
            border-top: 4px solid var(--primary);
        }

        .stat-card.success {
            border-top: 4px solid var(--success);
        }

        .stat-card.warning {
            border-top: 4px solid var(--warning);
        }

        .stat-card.danger {
            border-top: 4px solid var(--danger);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        /* Table */
        .recent-table {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .recent-table h3 {
            margin-bottom: 20px;
            color: var(--dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e9ecef;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-nuevo {
            background: #d4edda;
            color: #155724;
        }

        .status-proceso {
            background: #fff3cd;
            color: #856404;
        }

        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }

        .btn-view {
            background: var(--primary);
            color: white;
        }

        .btn-edit {
            background: var(--warning);
            color: white;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-cogs"></i> Panel de Administración</h1>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
            </div>
            <div>
                <div><strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong></div>
                <div style="font-size: 12px;"><?php echo htmlspecialchars($usuario['rol']); ?></div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>

    <!-- Container -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-menu">
                <li>
                    <a href="admin.php" class="nav-item active">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="solicitudes.php" class="nav-item">
                        <i class="fas fa-inbox nav-icon"></i>
                        Solicitudes
                        <?php if ($solicitudesNuevas > 0): ?>
                            <span style="background: var(--danger); color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                                <?php echo $solicitudesNuevas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="usuarios.php" class="nav-item">
                        <i class="fas fa-users nav-icon"></i>
                        Usuarios
                    </a>
                </li>
                <li>
                    <a href="proyectos.php" class="nav-item">
                        <i class="fas fa-project-diagram nav-icon"></i>
                        Proyectos
                    </a>
                </li>
                <li>
                    <a href="servicios.php" class="nav-item">
                        <i class="fas fa-concierge-bell nav-icon"></i>
                        Servicios
                    </a>
                </li>
                <li>
                    <a href="configuracion.php" class="nav-item">
                        <i class="fas fa-cog nav-icon"></i>
                        Configuración
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?> 👋</h2>
                <p>Panel de control y administración de Consultoría Web</p>
                <p style="color: #666; font-size: 14px; margin-top: 10px;">
                    <i class="fas fa-clock"></i> Último acceso: 
                    <?php echo date('d/m/Y H:i'); ?>
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <i class="fas fa-inbox fa-2x" style="color: var(--primary);"></i>
                    <div class="stat-number"><?php echo $totalSolicitudes; ?></div>
                    <div class="stat-label">Total de Solicitudes</div>
                </div>
                
                <div class="stat-card warning">
                    <i class="fas fa-exclamation-circle fa-2x" style="color: var(--warning);"></i>
                    <div class="stat-number"><?php echo $solicitudesNuevas; ?></div>
                    <div class="stat-label">Solicitudes Nuevas</div>
                </div>
                
                <div class="stat-card success">
                    <i class="fas fa-users fa-2x" style="color: var(--success);"></i>
                    <div class="stat-number"><?php echo $totalUsuarios; ?></div>
                    <div class="stat-label">Usuarios Registrados</div>
                </div>
                
                <div class="stat-card danger">
                    <i class="fas fa-user-shield fa-2x" style="color: var(--danger);"></i>
                    <div class="stat-number">1</div>
                    <div class="stat-label">Administradores Activos</div>
                </div>
            </div>

            <!-- Recent Solicitudes -->
            <div class="recent-table">
                <h3><i class="fas fa-history"></i> Últimas Solicitudes</h3>
                
                <?php if (isset($errorBD)): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">
                        <?php echo $errorBD; ?>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Empresa</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimasSolicitudes)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px;">
                                        No hay solicitudes recientes
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ultimasSolicitudes as $solicitud): ?>
                                <tr>
                                    <td>#<?php echo $solicitud['id']; ?></td>
                                    <td><?php echo htmlspecialchars($solicitud['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($solicitud['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($solicitud['empresa'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge status-nuevo">
                                            <?php echo $solicitud['estado']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($solicitud['fecha_creacion'])); ?></td>
                                    <td>
                                        <button class="btn-action btn-view">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <a href="solicitudes.php" style="text-decoration: none;">
                    <div style="background: var(--primary); color: white; padding: 15px 25px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-inbox"></i>
                        Ver todas las solicitudes
                    </div>
                </a>
                <a href="nuevo_proyecto.php" style="text-decoration: none;">
                    <div style="background: var(--success); color: white; padding: 15px 25px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-plus"></i>
                        Nuevo proyecto
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© <?php echo date('Y'); ?> Consultoría Web - Panel de Administración v1.0</p>
        <p style="font-size: 12px; margin-top: 5px;">
            <i class="fas fa-server"></i> 
            <?php 
            try {
                $pdo = obtenerConexion();
                $stmt = $pdo->query("SELECT VERSION() as version");
                $mysqlVersion = $stmt->fetch()['version'];
                echo "MySQL " . $mysqlVersion . " | ";
            } catch (PDOException $e) {
                // Ignorar error
            }
            ?>
            PHP <?php echo phpversion(); ?>
        </p>
    </div>

    <script>
        // Auto-refresh de notificaciones cada 60 segundos
        setInterval(function() {
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.nuevas > 0) {
                        // Actualizar contador de notificaciones
                        const notifElement = document.querySelector('.nav-item:nth-child(2) span');
                        if (notifElement) {
                            notifElement.textContent = data.nuevas;
                        }
                    }
                });
        }, 60000);
    </script>
</body>
</html>