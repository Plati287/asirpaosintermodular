<?php
define("MAIL_HOST",     "smtp.gmail.com");
define("MAIL_PORT",     587);
define("MAIL_USER",     "oscarpm2006@gmail.com");
define("MAIL_PASS",     "zjgurrmjxinstrwb");
define("MAIL_FROM",     "oscarpm2006@gmail.com");
define("MAIL_FROM_NAME","TechStore");
define("SITE_URL", "https://techstores.duckdns.org");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . "/../vendor/autoload.php";
function enviarEmail($destinatario, $nombre, $asunto, $cuerpo_html) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = "tls";
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = "UTF-8";
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($destinatario, $nombre);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html;
        $mail->AltBody = strip_tags($cuerpo_html);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando email: " . $mail->ErrorInfo);
        return false;
    }
}