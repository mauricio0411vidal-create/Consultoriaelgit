<?php
require_once __DIR__ . "/config/correo.php";

$ok = enviarCorreoConfirmacion("TU_CORREO_DESTINO@gmail.com", "Armando");
echo $ok ? "ENVIADO" : "FALLO";
