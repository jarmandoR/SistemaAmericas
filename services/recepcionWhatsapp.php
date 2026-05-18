<?php
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ALL);
// date_default_timezone_set('America/Bogota');

function write_log($contenido) {
    $fechatiempo = date("Y-m-d H:i:s");
    file_put_contents("log_webhook.txt", "[$fechatiempo] $contenido\n", FILE_APPEND);
}

function clienteAutorizoDatos($conn, $telefonoCliente) {
    $stmt = $conn->prepare("
        SELECT COUNT(id) AS cantidad
        FROM registro
        WHERE telefono_wa = ?
            AND (
                LOWER(mensaje_recibido) LIKE '%acepto%'
                OR LOWER(mensaje_recibido) LIKE '%autorizo%'
                OR LOWER(mensaje_recibido) LIKE '%si autorizo%'
                OR LOWER(mensaje_recibido) LIKE '%sí autorizo%'
                OR LOWER(mensaje_recibido) LIKE '%estoy de acuerdo%'
                OR LOWER(mensaje_recibido) LIKE '%de acuerdo%'
                OR LOWER(mensaje_recibido) LIKE '%confirmo%'
            )
            AND LOWER(mensaje_recibido) NOT LIKE '%no acepto%'
            AND LOWER(mensaje_recibido) NOT LIKE '%no autorizo%'
    ");
    if (!$stmt) {
        write_log("Error preparando consulta de autorizacion: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $telefonoCliente);
    if (!$stmt->execute()) {
        write_log("Error consultando autorizacion del cliente: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $cantidad = 0;
    $stmt->bind_result($cantidad);
    $stmt->fetch();
    $stmt->close();

    return ((int) $cantidad) > 0;
}

function mensajeAceptaTratamientoDatos($mensaje) {
    $mensajeNormalizado = strtolower(trim($mensaje));

    if (strpos($mensajeNormalizado, 'no acepto') !== false || strpos($mensajeNormalizado, 'no autorizo') !== false) {
        return false;
    }

    $patronesAceptacion = [
        'acepto',
        'autorizo',
        'si autorizo',
        'sí autorizo',
        'estoy de acuerdo',
        'de acuerdo',
        'confirmo'
    ];

    foreach ($patronesAceptacion as $patron) {
        if (strpos($mensajeNormalizado, $patron) !== false) {
            return true;
        }
    }

    return false;
}

function construirMensajeAutorizacion() {
    return "Hola! Bienvenido a Edmar Americas´s 🛒✨\n\n"
        . "Para atenderte por este canal necesitamos tu autorizacion para tratar tus datos personales, como tu numero de telefono, nombre, direccion y la informacion necesaria para gestionar pedidos, entregas, soporte y comunicaciones relacionadas con nuestro servicio. 🔐\n\n"
        . "Puedes consultar nuestra politica de privacidad aqui: 📄\n"
        . "https://edmaramericas.com/sistema/views/politicas-privacidad.php\n\n"
        . "Si estas de acuerdo, responde *ACEPTO* ✅ y con gusto continuamos con tu atencion.";
}

function construirMensajePrincipal($link) {
    return "Bienvenido a Edmar Americas´s! 🛒✨\n\n"
        . "Tenemos licores 🍷, cervezas 🍺, bebidas 🥤, mezcladores, snacks 🍟 y muchos mas productos para tu negocio, reunion o celebracion.\n\n"
        . "Haz tu pedido facil y rapido tocando el boton de abajo. 🚚\n\n"
        . "Necesitas ayuda o mas informacion? 💬\n"
        . "Escribenos por WhatsApp al 3107647676 y uno de nuestros asesores te atendera con gusto. ✅";
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
    $puedeVerCatalogo = clienteAutorizoDatos($conn, $telefonoCliente) || mensajeAceptaTratamientoDatos($mensaje);
    $respuestaTexto = $puedeVerCatalogo
        ? construirMensajePrincipal($link)
        : construirMensajeAutorizacion();
    $tipoMensaje = $puedeVerCatalogo ? 2 : 1;

    $sender->enviar($mensaje, $respuestaTexto, $id, $timestamp, $telefonoCliente, $link, $tipoMensaje);
    write_log("Mensaje de respuesta enviado a $telefonoCliente");
}
