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

$nombre = trim($entrada["nombre"] ?? "");
$especialidad = trim($entrada["especialidad"] ?? "");
$biografia = trim($entrada["biografia"] ?? "");
$foto = trim($entrada["foto"] ?? "");
$visible = (int)($entrada["visible"] ?? 1);

if ($nombre === "" || $especialidad === "") {
  http_response_code(422);
  echo json_encode(["ok" => false, "error" => "Nombre y especialidad obligatorios"]);
  exit;
}

try {
  $pdo = obtenerConexion();

  $sql = "INSERT INTO equipo (nombre_completo, especialidad, biografia, foto, visible)
          VALUES (:n, :e, :b, :f, :v)";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ":n" => $nombre,
    ":e" => $especialidad,
    ":b" => $biografia ?: null,
    ":f" => $foto ?: null,
    ":v" => $visible
  ]);

  echo json_encode(["ok" => true, "id" => (int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => "Error al crear integrante"]);
}
