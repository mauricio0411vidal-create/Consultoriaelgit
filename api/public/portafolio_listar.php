<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");
require_once dirname(__DIR__, 2) . "/config/db.php";

try {
  $pdo = obtenerConexion();

  $sql = "SELECT id, titulo, resumen, descripcion, imagen_portada, url_proyecto
          FROM proyectos
          WHERE publicado = 1
          ORDER BY id DESC";

  $data = $pdo->query($sql)->fetchAll();

  echo json_encode(["ok" => true, "data" => $data]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => "Error al listar proyectos"]);
}
