<?php
session_start();
require_once __DIR__ . "/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($correo) || empty($password)) {
        $error = "Por favor, ingresa tu correo y contraseña.";
    } else {
        try {
            $pdo = obtenerConexion();
            $sql = "SELECT id, nombre_completo, correo, contrasena_hash, rol, activo FROM usuarios WHERE correo = :correo";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":correo" => $correo]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                // Verificar si el usuario está activo
                if (!$usuario['activo']) {
                    $error = "Tu cuenta está desactivada. Contacta al administrador.";
                }
                // Verificar contraseña - MODIFICADO para aceptar texto plano temporalmente
                elseif ($password === $usuario['contrasena_hash'] || password_verify($password, $usuario['contrasena_hash'])) {
                    // Si la contraseña está en texto plano, actualizarla a hash
                    if ($password === $usuario['contrasena_hash']) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $updateHash = "UPDATE usuarios SET contrasena_hash = :hash WHERE id = :id";
                        $stmtHash = $pdo->prepare($updateHash);
                        $stmtHash->execute([":hash" => $newHash, ":id" => $usuario['id']]);
                    }
                    
                    // Iniciar sesión
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
                    $_SESSION['usuario_correo'] = $usuario['correo'];
                    $_SESSION['usuario_rol'] = $usuario['rol'] ?? 'admin'; // Valor por defecto
                    $_SESSION['login_time'] = time();
                    
                    // Actualizar último login
                    $updateSql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([":id" => $usuario['id']]);
                    
                    // Redirigir al admin
                    header("Location: admin.php");
                    exit();
                } else {
                    $error = "Correo o contraseña incorrectos.";
                }
            } else {
                $error = "Correo o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Administración</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            position: relative;
        }

        .header-login {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-button {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-button:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-login {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(102, 126, 234, 0.4);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .info {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .info a {
            color: #667eea;
            text-decoration: none;
        }

        .info a:hover {
            text-decoration: underline;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 14px;
            padding: 5px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .button-group .back-button {
            flex: 1;
        }

        .button-group .btn-login {
            flex: 2;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .header-login {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .button-group {
                flex-direction: column;
            }
        }

        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header con botón de regreso -->
        <div class="header-login">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Volver al Sitio
            </a>
            <div>
                <h1 style="color: #667eea; font-size: 24px;">🔒</h1>
            </div>
        </div>

        <div class="logo">
            <h1>Consultoría Web</h1>
            <p>Panel de Administración</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['logout']) && $_GET['logout'] == '1'): ?>
            <div class="success">✅ Has cerrado sesión correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['session']) && $_GET['session'] == 'expired'): ?>
            <div class="warning">⏰ Tu sesión ha expirado. Por favor, inicia sesión nuevamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['access']) && $_GET['access'] == 'denied'): ?>
            <div class="error">🚫 Acceso denegado. Necesitas iniciar sesión.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" 
                       value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" 
                       required placeholder="Ingresa tu correo">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" 
                           required placeholder="Ingresa tu contraseña"
                           value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        👁️
                    </button>
                </div>
            </div>

            <div class="button-group">
                <a href="index.php" class="back-button">
                    <i class="fas fa-home"></i>
                    Inicio
                </a>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </div>
        </form>

        <div class="info">
            <p>¿Problemas para acceder? <a href="mailto:soporte@consultoria.com">Contactar soporte</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = '👁️‍🗨️';
                toggleButton.title = 'Ocultar contraseña';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = '👁️';
                toggleButton.title = 'Mostrar contraseña';
            }
        }

        // Auto-focus en el campo de correo
        document.getElementById('correo').focus();
        
        // Enter para enviar formulario
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
        
        // Mostrar/ocultar contraseña con tecla Alt
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key === 'p') {
                e.preventDefault();
                togglePassword();
            }
        });
        
        // Cargar Font Awesome si no está cargado
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const faLink = document.createElement('link');
            faLink.rel = 'stylesheet';
            faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
            document.head.appendChild(faLink);
        }
    </script>
</body>
</html>