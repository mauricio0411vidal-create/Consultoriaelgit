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

$solicitud_id = (int)($_GET["solicitud_id"] ?? 0);

if ($solicitud_id <= 0) {
    http_response_code(422);
    echo json_encode(["ok" => false, "error" => "ID inválido"]);
    exit;
}

try {
    $pdo = obtenerConexion();

    $sql = "SELECT
              tipo_remitente,
              mensaje,
              fecha_envio
            FROM mensajes_contacto
            WHERE solicitud_id = :id
            ORDER BY fecha_envio ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":id" => $solicitud_id]);

    echo json_encode([
        "ok" => true,
        "data" => $stmt->fetchAll()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Error al obtener mensajes"]);
}
