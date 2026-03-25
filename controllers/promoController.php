<?php
require_once "../models/Promo.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $metod = $_POST["action"];




    $user = new Promo();





    if ($metod=="insert") {
                // Capturar los datos del formulario
        $id = isset($_POST["id"]) ? $_POST["id"] : null;
        $titulo = $_POST["titulo"];
        $patrocinador = $_POST["patrocinador"];
        $estado = $_POST["estado"];
        $descripcion = $_POST["descripcion"];
    
        if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
            $nombreOriginal = $_FILES["imagen"]["name"];
            $tmpPath = $_FILES["imagen"]["tmp_name"];
        
            $nombreImagen = uniqid("img_") . "_" . basename($nombreOriginal); // evita colisiones
            $destino = "../uploads/" . $nombreImagen; // asegúrate de que esta carpeta exista y tenga permisos
        
            if (!move_uploaded_file($tmpPath, $destino)) {
                die("Error al mover la imagen al servidor.");
            }
        } else {
            die("No se subió ninguna imagen o hubo un error al subirla.");
        }
        // Si no hay ID, significa que estamos registrando un nuevo usuario
        // if ($user->registerPromo($titulo, $patrocinador, $estado, $nombreImagen, $descripcion)) {
        //     header("Location: ../views/user-list.php?success=1");
        // } else {
        //     header("Location: ../views/user-new.html?error=1");
        // }
        $promo = new Promo();
        $insertado = $promo->registerPromo($titulo, $patrocinador, $estado, $nombreImagen, $descripcion);
        if ($insertado) {
            echo json_encode([
                "success" => true,
                "message" => "Promoción registrada correctamente"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se pudo registrar la promoción"
            ]);
        }
        exit;
    }elseif ($metod=="update") {
        // Capturar los datos del formulario
        $id = isset($_POST["id"]) ? $_POST["id"] : null;
        $titulo = $_POST["titulo"];
        $patrocinador = $_POST["patrocinador"];
        $estado = $_POST["estado"];
        $descripcion = $_POST["descripcion"];

        if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
            $nombreOriginal = $_FILES["imagen"]["name"];
            $tmpPath = $_FILES["imagen"]["tmp_name"];
        
            $nombreImagen = uniqid("img_") . "_" . basename($nombreOriginal); // evita colisiones
            $destino = "../uploads/promociones" . $nombreImagen; // asegúrate de que esta carpeta exista y tenga permisos
        
            if (!move_uploaded_file($tmpPath, $destino)) {
                die("Error al mover la imagen al servidor.");
            }
        } else {
            die("No se subió ninguna imagen o hubo un error al subirla.");
        }
        // Si el ID está presente, significa que estamos editando
        $promo = new Promo();
        $modificado =$promo ->updatePromo($id, $titulo, $patrocinador, $estado, $nombreImagen, $descripcion);
            
        if ($modificado) {
            echo json_encode([
                "success" => true,
                "message" => "Promoción registrada correctamente"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No se pudo registrar la promoción"
            ]);
        }
        exit;
    } elseif ($metod=="delete") {
        // Si el ID está presente, significa que estamos editando
        if ($user->deletePromo($id)) {
            header("Location: ../views/user-list.php?success=edit");
        } else {
            header("Location: ../views/user-list.php?id=$id&error=1");
        }
    }elseif ($metod=="mostrar") {
        $promo = new Promo();
        $promociones = $promo->getAllPromos();
        header('Content-Type: application/json');
        echo json_encode($promociones);
    }elseif ($metod=="enviarPromo") {

        $id = $_POST['id'] ?? null;
        $texto = $_POST['texto'] ?? null;
        $imagen1 = $_POST['imagen1'] ?? null;

        $promo = new Promo();
        $resultados = $promo->enviarPromo($id, $texto, $imagen1);

        // Puedes devolver los resultados para verificar desde el frontend
        echo json_encode([
            'success' => true,
            'resultados' => $resultados
        ]);
        
    }
    exit();
}


?>