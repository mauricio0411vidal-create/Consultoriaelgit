<?php
declare(strict_types=1);

session_start();
header("Content-Type: application/json; charset=utf-8");

require_once dirname(__DIR__, 2) . "/config/db.php";

$entrada = json_decode(file_get_contents("php://input"), true);

$correo = trim($entrada["correo"] ?? "");
$contrasena = trim($entrada["contrasena"] ?? "");

if ($correo === "" || $contrasena === "") {
    http_response_code(422);
    echo json_encode([
        "ok" => false,
        "error" => "Datos incompletos"
    ]);
    exit;
}

try {
    $pdo = obtenerConexion();

    $sql = "SELECT id, nombre_completo
            FROM usuarios
            WHERE correo = :correo
              AND contrasena_hash = :contrasena
              AND activo = 1
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":correo" => $correo,
        ":contrasena" => $contrasena
    ]);

    $usuario = $stmt->fetch();

    if (!$usuario) {
        http_response_code(401);
        echo json_encode([
            "ok" => false,
            "error" => "Credenciales incorrectas"
        ]);
        exit;
    }

    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nombre"] = $usuario["nombre_completo"];

    echo json_encode([
        "ok" => true,
        "usuario" => $usuario["nombre_completo"]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "error" => "Error del servidor"
    ]);
}
