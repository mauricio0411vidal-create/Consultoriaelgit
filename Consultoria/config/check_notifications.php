<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

verificarAutenticacion();

header('Content-Type: application/json');

try {
    $pdo = obtenerConexion();
    $stmt = $pdo->query("SELECT COUNT(*) as nuevas FROM solicitudes_contacto WHERE estado = 'NUEVO'");
    $result = $stmt->fetch();
    
    echo json_encode(['nuevas' => $result['nuevas']]);
} catch (PDOException $e) {
    echo json_encode(['nuevas' => 0, 'error' => $e->getMessage()]);
}
?>