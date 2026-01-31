<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../libs/PHPMailer/Exception.php";
require_once __DIR__ . "/../libs/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../libs/PHPMailer/SMTP.php";

function enviarCorreoConfirmacion(string $correoDestino, string $nombre): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';

        // Configuración SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "consultoriaelgit@gmail.com";          
        $mail->Password = "wgue ikot soxq ofja";   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Emisor y receptor
        $mail->setFrom("consultoriaelgit@gmail.com", "Consultoría Web");
        $mail->addAddress($correoDestino, $nombre);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = "Hemos recibido tu solicitud";
        $mail->Body = "
          <p>Hola <b>" . htmlspecialchars($nombre) . "</b>,</p>
          <p>Hemos recibido tu solicitud correctamente.</p>
          <p>En un momento uno de nuestros asesores se pondrá en contacto contigo.</p>
          <p>Saludos,<br>Equipo de Consultoría Web</p>
        ";
        $mail->AltBody = "Hola $nombre, recibimos tu solicitud. En breve nos pondremos en contacto contigo. - Consultoría Web";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Si quieres ver el error exacto en logs:
        // error_log("Error PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}
