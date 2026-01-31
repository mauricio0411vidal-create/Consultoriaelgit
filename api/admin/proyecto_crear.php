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
$resumen = trim($entrada["resumen"] ?? "");
$descripcion = trim($entrada["descripcion"] ?? "");
$imagen = trim($entrada["imagen"] ?? "");
$url = trim($entrada["url"] ?? "");
$publicado = (int)($entrada["publicado"] ?? 1);

if ($titulo === "") {
  http_response_code(422);
  echo json_encode(["ok" => false, "error" => "Título obligatorio"]);
  exit;
}

try {
  $pdo = obtenerConexion();

  $sql = "INSERT INTO proyectos (titulo, resumen, descripcion, imagen_portada, url_proyecto, publicado)
          VALUES (:t, :r, :d, :i, :u, :p)";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ":t" => $titulo,
    ":r" => $resumen ?: null,
    ":d" => $descripcion ?: null,
    ":i" => $imagen ?: null,
    ":u" => $url ?: null,
    ":p" => $publicado
  ]);

  echo json_encode(["ok" => true, "id" => (int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => "Error al crear proyecto"]);
}
