<?php
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ALL);
// date_default_timezone_set('America/Bogota');

function write_log($contenido) {
    $fechatiempo = date("Y-m-d H:i:s");
    file_put_contents("log_webhook.txt", "[$fechatiempo] $contenido\n", FILE_APPEND);
}

function clienteTieneRegistro($conn, $telefonoCliente) {
    $stmt = $conn->prepare("SELECT COUNT(id) AS cantidad FROM registro WHERE telefono_wa = ?");
    if (!$stmt) {
        write_log("Error preparando consulta de historial: " . $conn->error);
        return true;
    }

    $stmt->bind_param("s", $telefonoCliente);
    if (!$stmt->execute()) {
        write_log("Error consultando historial del cliente: " . $stmt->error);
        $stmt->close();
        return true;
    }

    $cantidad = 0;
    $stmt->bind_result($cantidad);
    $stmt->fetch();
    $stmt->close();

    return ((int) $cantidad) > 0;
}

function construirMensajeAutorizacion() {
    return "Hola, bienvenido a Edmar Americas.\n\n"
        . "Para atender tu solicitud por este canal necesitamos tu autorizacion para tratar tus datos personales, como tu numero de telefono, nombre, direccion y la informacion necesaria para gestionar pedidos, entregas, soporte y comunicaciones relacionadas con nuestro servicio.\n\n"
        . "Puedes consultar nuestra politica de privacidad aqui:\n"
        . "https://edmaramericas.com/sistema/views/politicas-privacidad.php\n\n"
        . "Si estas de acuerdo, responde a este chat y con gusto continuamos con tu atencion.";
}

function construirMensajePrincipal($link) {
    return "Bienvenido a Edmar Americas.\n\n"
        . "Encuentra licores, cervezas, bebidas, mezcladores, snacks y otros productos para tu negocio, reunion o celebracion.\n\n"
        . "Ver catalogo y comprar ahora:\n"
        . "$link\n\n"
        . "Necesitas ayuda o mas informacion?\n"
        . "Escribenos por WhatsApp al 3107647676 y uno de nuestros asesores te atendera.";
}

// VALIDAR WEBHOOK
$token = 'Multilicoreslicor25';
if (isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === $token) {
    echo $_GET['hub_challenge'];
    write_log("Webhook verificado correctamente.");
    exit;
}

// LEER Y DECODIFICAR MENSAJE ENTRANTE
$inputRaw = file_get_contents("php://input");
$respuesta = json_decode($inputRaw, true);

// Log del JSON recibido completo
write_log("Entrada RAW: " . $inputRaw);

// Validar si el mensaje es de texto
if (!isset($respuesta['entry'][0]['changes'][0]['value']['messages'])) {
    write_log("No es un mensaje entrante valido.");
    exit;
}

$mensajeData = $respuesta['entry'][0]['changes'][0]['value']['messages'][0];
if (!isset($mensajeData['text']['body'])) {
    write_log("El mensaje no contiene texto (puede ser imagen, audio, etc).");
    exit;
}

$mensaje = $mensajeData['text']['body'];
$telefonoCliente = $mensajeData['from'];
$id = $mensajeData['id'];
$timestamp = $mensajeData['timestamp'];

require_once 'conexion.php';
require_once 'WhatsappSender.php';

$sender = new WhatsappSender($conn);

// Log del mensaje recibido
write_log("Mensaje recibido de $telefonoCliente: $mensaje");

if ($mensaje != null) {
    $link = "https://edmaramericas.com/sistema/views/categorias.php?idCli=$telefonoCliente";
    $respuestaTexto = clienteTieneRegistro($conn, $telefonoCliente)
        ? construirMensajePrincipal($link)
        : construirMensajeAutorizacion();

    $sender->enviar($mensaje, $respuestaTexto, $id, $timestamp, $telefonoCliente, $link, 1);
    write_log("Mensaje de respuesta enviado a $telefonoCliente");
}
