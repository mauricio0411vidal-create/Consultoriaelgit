<?php
declare(strict_types=1);

session_start();
header("Content-Type: application/json; charset=utf-8");

require_once dirname(__DIR__, 2) . "/config/db.php";

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "No autorizado"]);
    exit;
}

$entrada = json_decode(file_get_contents("php://input"), true);

$solicitud_id = (int)($entrada["solicitud_id"] ?? 0);
$mensaje = trim($entrada["mensaje"] ?? "");

if ($solicitud_id <= 0 || $mensaje === "") {
    http_response_code(422);
    echo json_encode(["ok" => false, "error" => "Datos inválidos"]);
    exit;
}

try {
    $pdo = obtenerConexion();

    $sql = "INSERT INTO mensajes_contacto
              (solicitud_id, tipo_remitente, mensaje)
            VALUES
              (:solicitud_id, 'ADMIN', :mensaje)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":solicitud_id" => $solicitud_id,
        ":mensaje" => $mensaje
    ]);

    echo json_encode(["ok" => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Error al guardar respuesta"]);
}
