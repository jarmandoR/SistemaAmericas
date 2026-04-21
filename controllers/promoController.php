<?php
require_once "../models/Promo.php";

function responderJson($success, $message = "", $extra = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $extra));
    exit;
}

function normalizarEstado($estado) {
    if ($estado === "Activa" || $estado === "1" || $estado === 1) {
        return 1;
    }

    return 0;
}

function subirImagen($requerida = false) {
    if (!isset($_FILES["imagen"]) || $_FILES["imagen"]["error"] === UPLOAD_ERR_NO_FILE) {
        if ($requerida) {
            responderJson(false, "Debe subir una imagen para la promocion.");
        }

        return "";
    }

    if ($_FILES["imagen"]["error"] !== UPLOAD_ERR_OK) {
        responderJson(false, "Hubo un error al subir la imagen.");
    }

    $nombreOriginal = $_FILES["imagen"]["name"];
    $tmpPath = $_FILES["imagen"]["tmp_name"];
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extensionesPermitidas = ["jpg", "jpeg", "png", "gif", "webp"];

    if (!in_array($extension, $extensionesPermitidas, true)) {
        responderJson(false, "Formato de imagen no permitido.");
    }

    $nombreImagen = uniqid("promo_", true) . "." . $extension;
    $directorio = "../assets/img/licores/promos/";
    $destino = $directorio . $nombreImagen;

    if (!is_dir($directorio)) {
        responderJson(false, "No existe la carpeta de imagenes de promociones.");
    }

    if (!move_uploaded_file($tmpPath, $destino)) {
        responderJson(false, "Error al mover la imagen al servidor.");
    }

    return $nombreImagen;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responderJson(false, "Metodo no permitido.");
}

$metodo = $_POST["action"] ?? "";
$promo = new Promo();

try {
    if ($metodo === "insert" || $metodo === "update") {
        $id = $_POST["id"] ?? null;
        $titulo = trim($_POST["titulo"] ?? "");
        $descripcion = trim($_POST["descripcion"] ?? "");
        $codigo = (int)($_POST["codigo"] ?? 0);
        $precioUnidad = (int)($_POST["precio_unidad_producto"] ?? 0);
        $precioPaca = (int)($_POST["precio_paca_producto"] ?? 0);
        $actiUnidad = (int)($_POST["acti_Unidad"] ?? 0);
        $estado = normalizarEstado($_POST["estado"] ?? 1);
        $prioridad = (int)($_POST["prioridad"] ?? 0);

        if ($titulo === "" || $descripcion === "") {
            responderJson(false, "El titulo y la descripcion son obligatorios.");
        }

        if ($codigo <= 0) {
            responderJson(false, "El codigo del producto es obligatorio.");
        }

        $nombreImagen = subirImagen($metodo === "insert");

        if ($metodo === "insert") {
            $guardado = $promo->registerPromo(
                $titulo,
                $descripcion,
                $codigo,
                $precioUnidad,
                $precioPaca,
                $actiUnidad,
                $nombreImagen,
                $estado,
                $prioridad
            );

            responderJson($guardado, $guardado ? "Promocion registrada correctamente." : "No se pudo registrar la promocion.");
        }

        if (empty($id)) {
            responderJson(false, "No se recibio el ID de la promocion.");
        }

        $guardado = $promo->updatePromo(
            $id,
            $titulo,
            $descripcion,
            $codigo,
            $precioUnidad,
            $precioPaca,
            $actiUnidad,
            $nombreImagen,
            $estado,
            $prioridad
        );

        responderJson($guardado, $guardado ? "Promocion actualizada correctamente." : "No se pudo actualizar la promocion.");
    }

    if ($metodo === "delete") {
        $id = $_POST["id"] ?? null;
        if (empty($id)) {
            responderJson(false, "No se recibio el ID de la promocion.");
        }

        $eliminado = $promo->deletePromo($id);
        responderJson($eliminado, $eliminado ? "Promocion eliminada correctamente." : "No se pudo eliminar la promocion.");
    }

    if ($metodo === "cambiarEstado") {
        $id = $_POST["id"] ?? null;
        $estado = normalizarEstado($_POST["estado"] ?? 0);

        if (empty($id)) {
            responderJson(false, "No se recibio el ID de la promocion.");
        }

        $actualizado = $promo->updateEstadoPromo($id, $estado);
        responderJson($actualizado, $actualizado ? "Estado actualizado correctamente." : "No se pudo actualizar el estado.");
    }

    if ($metodo === "mostrar") {
        responderJson(true, "", ["promociones" => $promo->getAllPromos()]);
    }

    if ($metodo === "enviarPromo") {
        $id = $_POST['id'] ?? null;
        $texto = $_POST['texto'] ?? null;
        $imagen1 = $_POST['imagen1'] ?? null;
        $resultados = $promo->enviarPromo($id, $texto, $imagen1);

        responderJson(true, "Promocion enviada correctamente.", [
            'resultados' => $resultados
        ]);
    }

    responderJson(false, "Accion no valida.");
} catch (Exception $e) {
    responderJson(false, "Error del servidor: " . $e->getMessage());
}
?>
