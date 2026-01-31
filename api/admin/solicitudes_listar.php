<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

require_once dirname(__DIR__, 2) . "/config/db.php";

try {
    $pdo = obtenerConexion();

    $sql = "SELECT
              id,
              nombre,
              correo,
              telefono,
              empresa,
              estado,
              fecha_creacion
            FROM solicitudes_contacto
            ORDER BY fecha_creacion DESC";

    $stmt = $pdo->query($sql);
    $solicitudes = $stmt->fetchAll();

    echo json_encode([
        "ok" => true,
        "total" => count($solicitudes),
        "data" => $solicitudes
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "error" => "Error al obtener solicitudes"
    ]);
}
