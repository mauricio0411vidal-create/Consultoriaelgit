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

$titulo = trim($entrada["titulo"] ?? "");
$slug = trim($entrada["slug"] ?? "");
$descripcion_corta = trim($entrada["descripcion_corta"] ?? "");
$descripcion_larga = trim($entrada["descripcion_larga"] ?? "");
$icono = trim($entrada["icono"] ?? "");
$orden = (int)($entrada["orden"] ?? 0);
$activo = (int)($entrada["activo"] ?? 1);

if ($titulo === "" || $slug === "") {
  http_response_code(422);
  echo json_encode(["ok" => false, "error" => "Título y slug son obligatorios"]);
  exit;
}

try {
  $pdo = obtenerConexion();

  $sql = "INSERT INTO servicios (slug, titulo, descripcion_corta, descripcion_larga, icono, orden, activo)
          VALUES (:slug, :titulo, :dc, :dl, :icono, :orden, :activo)";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ":slug" => $slug,
    ":titulo" => $titulo,
    ":dc" => $descripcion_corta !== "" ? $descripcion_corta : null,
    ":dl" => $descripcion_larga !== "" ? $descripcion_larga : null,
    ":icono" => $icono !== "" ? $icono : null,
    ":orden" => $orden,
    ":activo" => $activo
  ]);

  echo json_encode(["ok" => true, "id" => (int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => "Error al crear servicio"]);
}
