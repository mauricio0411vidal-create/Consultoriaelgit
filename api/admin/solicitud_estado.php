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

$id = (int)($entrada["id"] ?? 0);
$estado = $entrada["estado"] ?? "";

$permitidos = ["NUEVO", "EN_PROCESO", "CERRADO"];

if ($id <= 0 || !in_array($estado, $permitidos, true)) {
    http_response_code(422);
    echo json_encode(["ok" => false, "error" => "Datos inválidos"]);
    exit;
}

try {
    $pdo = obtenerConexion();

    $sql = "UPDATE solicitudes_contacto
            SET estado = :estado
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":estado" => $estado,
        ":id" => $id
    ]);

    echo json_encode(["ok" => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Error al actualizar"]);
}
