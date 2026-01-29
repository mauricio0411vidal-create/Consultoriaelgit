<?php
declare(strict_types=1);

session_start();
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode(["ok" => false, "error" => "Método no permitido"]);
  exit;
}

require_once dirname(__DIR__, 2) . "/config/db.php";

$entrada = $_POST;
$correo = trim((string)($entrada["correo"] ?? ""));
$contrasena = (string)($entrada["contrasena"] ?? "");

if ($correo === "" || $contrasena === "") {
  http_response_code(422);
  echo json_encode(["ok" => false, "error" => "Correo y contraseña son obligatorios"]);
  exit;
}

try {
  $pdo = obtenerConexion();
  $stmt = $pdo->prepare("SELECT id, nombre_completo, correo, contrasena_hash, activo FROM usuarios WHERE correo = ? LIMIT 1");
  $stmt->execute([$correo]);
  $user = $stmt->fetch();

  if (!$user || (int)$user["activo"] !== 1) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Credenciales inválidas"]);
    exit;
  }

  if (!password_verify($contrasena, $user["contrasena_hash"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Credenciales inválidas"]);
    exit;
  }

  // Guardar sesión
  $_SESSION["admin"] = [
    "id" => (int)$user["id"],
    "nombre" => $user["nombre_completo"],
    "correo" => $user["correo"]
  ];

  echo json_encode(["ok" => true, "admin" => $_SESSION["admin"]]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => "Error del servidor"]);
}
