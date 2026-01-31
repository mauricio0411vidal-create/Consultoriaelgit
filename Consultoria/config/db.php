<?php
// config/db.php
declare(strict_types=1);

function obtenerConexion(): PDO {
    $host = "localhost";
    $bd   = "consultoria";
    $user = "root";
    $pass = ""; // en XAMPP normalmente está vacío

    $dsn = "mysql:host=$host;dbname=$bd;charset=utf8mb4";

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $opciones);
}
