<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode(["ok" => false, "error" => "Método no permitido"]);
  exit;
}

require_once __DIR__ . "/../../config/db.php";

$contenidoTipo = $_SERVER["CONTENT_TYPE"] ?? "";
$entrada = null;

if (stripos($contenidoTipo, "application/json") !== false) {
  $entrada = json_decode(file_get_contents("php://input"), true);
} else {
  $entrada = $_POST; // FormData / x-www-form-urlencoded
}

if (!is_array($entrada)) {
  http_response_code(400);
  echo json_encode(["ok" => false, "error" => "Entrada inválida", "debug" => ["content_type" => $contenidoTipo]]);
  exit;
}

$nombre   = trim((string)($entrada["nombre"] ?? ""));
$correo   = trim((string)($entrada["correo"] ?? ""));
$telefono = trim((string)($entrada["telefono"] ?? ""));
$empresa  = trim((string)($entrada["empresa"] ?? ""));
$mensaje  = trim((string)($entrada["mensaje"] ?? ""));

if ($nombre === "" || $correo === "") {
  http_response_code(422);
  echo json_encode([
    "ok" => false,
    "error" => "Nombre y correo son obligatorios",
    "debug" => ["recibido" => $entrada, "content_type" => $contenidoTipo]
  ]);
  exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(["ok" => false, "error" => "Correo inválido", "debug" => ["correo" => $correo]]);
  exit;
}

try {
  $pdo = obtenerConexion();

  // Confirmar a qué BD estás conectado
  $bdActual = $pdo->query("SELECT DATABASE() AS bd")->fetch()["bd"] ?? null;

  $sql = "INSERT INTO solicitudes_contacto (nombre, correo, telefono, empresa, mensaje, estado)
          VALUES (:nombre, :correo, :telefono, :empresa, :mensaje, 'NUEVO')";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ":nombre"   => $nombre,
    ":correo"   => $correo,
    ":telefono" => $telefono !== "" ? $telefono : null,
    ":empresa"  => $empresa !== "" ? $empresa : null,
    ":mensaje"  => $mensaje !== "" ? $mensaje : null,
  ]);

  echo json_encode([
    "ok" => true,
    "id" => (int)$pdo->lastInsertId(),
    "debug" => [
      "bd" => $bdActual,
      "content_type" => $contenidoTipo,
      "recibido" => $entrada
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "ok" => false,
    "error" => "Error del servidor (debug)",
    "debug" => [
      "mensaje" => $e->getMessage()
    ]
  ]);
}
