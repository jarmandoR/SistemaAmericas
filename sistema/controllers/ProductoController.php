<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar codificación UTF-8
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

require_once "../models/ProductoModel.php";

// Función para detectar el delimitador del CSV
function detectarDelimitador($archivo)
{
    $handle = fopen($archivo, "r");
    if ($handle) {
        $primeraLinea = fgets($handle);
        fclose($handle);

        $delimitadores = [',', ';', '\t', '|'];
        $maxColumnas = 0;
        $mejorDelimitador = ',';

        foreach ($delimitadores as $delim) {
            $columnas = str_getcsv($primeraLinea, $delim);
            if (count($columnas) > $maxColumnas) {
                $maxColumnas = count($columnas);
                $mejorDelimitador = $delim;
            }
        }

        return $mejorDelimitador;
    }
    return ',';
}

// Función para detectar y convertir encoding del archivo
function detectarYConvertirEncoding($contenido)
{
    $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'CP1252'];

    foreach ($encodings as $encoding) {
        if (mb_check_encoding($contenido, $encoding)) {
            if ($encoding !== 'UTF-8') {
                return mb_convert_encoding($contenido, 'UTF-8', $encoding);
            }
            return $contenido;
        }
    }

    // Si no se puede detectar, intentar convertir desde ISO-8859-1
    return mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
}

// Función para limpiar BOM (Byte Order Mark) si existe
function eliminarBOM($contenido)
{
    $bom = pack('H*', 'EFBBBF');
    $contenido = preg_replace("/^$bom/", '', $contenido);
    return $contenido;
}

// Función para limpiar y convertir valores con soporte UTF-8
function limpiarValor($valor, $tipo = 'string')
{
    // Primero asegurar que el valor esté en UTF-8
    if (!mb_check_encoding($valor, 'UTF-8')) {
        $valor = mb_convert_encoding($valor, 'UTF-8', 'auto');
    }

    // Limpiar espacios en blanco, incluyendo espacios Unicode
    $valor = preg_replace('/^[\s\xA0\x{00A0}\x{2000}-\x{200F}\x{2028}-\x{202F}]+|[\s\xA0\x{00A0}\x{2000}-\x{200F}\x{2028}-\x{202F}]+$/u', '', $valor);

    switch ($tipo) {
        case 'int':
            return is_numeric($valor) && $valor != '' ? (int)$valor : null;
        case 'float':
            // Manejar diferentes formatos de números con caracteres especiales
            $valor = str_replace([',', ' '], ['.', ''], $valor); // Cambiar coma decimal por punto y quitar espacios
            $valor = preg_replace('/[^\d.-]/', '', $valor); // Quitar caracteres no numéricos excepto punto y guión
            return is_numeric($valor) && $valor != '' ? (float)$valor : 0;
        default:
            // Normalizar caracteres Unicode si es necesario
            if (class_exists('Normalizer')) {
                $valor = Normalizer::normalize($valor, Normalizer::FORM_C);
            }
            return $valor;
    }
}

// Función mejorada para leer CSV con soporte UTF-8
function leerCSVConUTF8($archivo, $delimitador = ',')
{
    $datos = [];

    // Leer el archivo completo
    $contenido = file_get_contents($archivo);

    if ($contenido === false) {
        throw new Exception("No se pudo leer el archivo");
    }

    // Eliminar BOM si existe
    $contenido = eliminarBOM($contenido);

    // Detectar y convertir encoding
    $contenido = detectarYConvertirEncoding($contenido);

    // Dividir en líneas manteniendo UTF-8
    $lineas = preg_split('/\R/', $contenido);

    foreach ($lineas as $linea) {
        if (trim($linea) !== '') {
            // Usar str_getcsv con soporte UTF-8
            $fila = str_getcsv($linea, $delimitador, '"', '\\');

            // Limpiar cada campo
            $fila = array_map(function ($campo) {
                return limpiarValor($campo);
            }, $fila);

            $datos[] = $fila;
        }
    }

    return $datos;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["archivo_excel"])) {

    // Verificaciones básicas
    if (!isset($_FILES["archivo_excel"]) || $_FILES["archivo_excel"]["error"] !== UPLOAD_ERR_OK) {
        header("Location: ../views/Subir_excel_producto.php?error=" . urlencode("Error al subir archivo. Código: " . $_FILES["archivo_excel"]["error"]));
        exit;
    }

    $archivo = $_FILES["archivo_excel"]["tmp_name"];
    $nombreArchivo = $_FILES["archivo_excel"]["name"];
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    // Verificar extensión
    if (!in_array($extension, ['csv'])) {
        header("Location: ../views/Subir_excel_producto.php?error=" . urlencode("Solo se permiten archivos CSV"));
        exit;
    }

    // Verificar tamaño (5MB máximo)
    if ($_FILES["archivo_excel"]["size"] > 5 * 1024 * 1024) {
        header("Location: ../views/Subir_excel_producto.php?error=" . urlencode("El archivo no debe superar los 5MB"));
        exit;
    }

    if (is_uploaded_file($archivo)) {
        try {
            // Detectar delimitador
            $delimitador = detectarDelimitador($archivo);

            // Leer archivo CSV con soporte UTF-8
            $datos = leerCSVConUTF8($archivo, $delimitador);

            if (empty($datos)) {
                header("Location: ../views/Subir_excel_producto.php?error=" . urlencode("El archivo está vacío"));
                exit;
            }

            // Detectar y remover encabezado
            $primeraFila = $datos[0];
            $tieneEncabezado = false;

            // Verificar si la primera fila es encabezado
            if (!empty($primeraFila[0])) {
                $primerValor = mb_strtolower(trim($primeraFila[0]), 'UTF-8');
                // Es encabezado si contiene palabras clave
                if (
                    !is_numeric($primerValor) ||
                    mb_strpos($primerValor, 'id', 0, 'UTF-8') !== false ||
                    mb_strpos($primerValor, 'codigo', 0, 'UTF-8') !== false ||
                    mb_strpos($primerValor, 'producto', 0, 'UTF-8') !== false ||
                    mb_strpos($primerValor, 'descripcion', 0, 'UTF-8') !== false
                ) {
                    $tieneEncabezado = true;
                    array_shift($datos);
                }
            }

            $producto = new Producto();
            $insertados = 0;
            $actualizados = 0;
            $errores = 0;
            $erroresDetalle = [];
            $filasVacias = 0;
            $preciosActualizados = 0;

            foreach ($datos as $indice => $fila) {
                $filaNumero = $indice + 1 + ($tieneEncabezado ? 1 : 0);

                try {
                    // Saltar filas completamente vacías
                    if (empty(array_filter($fila, function ($val) {
                        return trim($val) !== '';
                    }))) {
                        $filasVacias++;
                        continue;
                    }

                    // Expandir array si tiene menos de 10 columnas
                    while (count($fila) < 10) {
                        $fila[] = '';
                    }

                    // Mapear datos según tu estructura de BD con limpieza UTF-8
                    $id_producto             = limpiarValor($fila[0], 'int');
                    $codigo_productos        = limpiarValor($fila[1]);
                    $descripcion_producto    = limpiarValor($fila[2]);
                    $cantidad_paca_producto  = limpiarValor($fila[3], 'float');
                    $precio_unidad_producto  = limpiarValor($fila[4], 'float');
                    $precio_paca_producto    = limpiarValor($fila[5], 'float');
                    $id_cate_producto        = limpiarValor($fila[6], 'int') ?: 1;
                    $acti_Unidad             = limpiarValor($fila[7]) ?: '1';
                    $imagen_producto         = limpiarValor($fila[8]);
                    $estado_producto         = limpiarValor($fila[9]) ?: '1';

                    // Validaciones básicas
                    $erroresFila = [];

                    if (empty($codigo_productos)) {
                        $erroresFila[] = "código de producto vacío";
                    }

                    if (empty($descripcion_producto)) {
                        $erroresFila[] = "descripción vacía";
                    }

                    if ($precio_unidad_producto <= 0) {
                        $erroresFila[] = "precio_unidad_producto debe ser mayor a 0";
                    }

                    if (!empty($erroresFila)) {
                        $errores++;
                        $erroresDetalle[] = "Fila $filaNumero: " . implode(', ', $erroresFila);
                        continue;
                    }

                    // LÓGICA PRINCIPAL: Buscar por código de producto
                    $productoExistente = null;

                    // 1. Si hay ID específico, verificar por ID
                    if ($id_producto && $producto->existeProducto($id_producto)) {
                        $productoExistente = $producto->obtenerProductoPorId($id_producto);
                    }

                    // 2. Si no se encontró por ID o no hay ID, buscar por código
                    if (!$productoExistente) {
                        $productoExistente = $producto->obtenerProductoPorCodigo($codigo_productos);
                    }

                    if ($productoExistente) {                        
                        // ACTUALIZAR producto existente
                        $actualizar = false;
                        $cambios = [];

                        // Verificar si hay cambios en los precios
                        if (
                            abs($productoExistente['precio_unidad_producto'] - $precio_unidad_producto) > 0.01 ||
                            abs($productoExistente['precio_paca_producto'] - $precio_paca_producto) > 0.01
                        ) {

                            if ($producto->actualizarProductoPorCodigo(
                                $codigo_productos,
                                $precio_unidad_producto,
                                $precio_paca_producto
                            )) {
                                $actualizados++;
                                $preciosActualizados++;
                            } else {
                                $errores++;
                                $erroresDetalle[] = "Fila $filaNumero: error al actualizar precios para código '$codigo_productos'";
                            }
                        }
                    } else {
                        // INSERTAR nuevo producto
                        if ($producto->insertarProducto(
                            $codigo_productos,
                            $descripcion_producto,
                            $cantidad_paca_producto,
                            $precio_unidad_producto,
                            $precio_paca_producto,
                            $id_cate_producto,
                            $acti_Unidad,
                            $imagen_producto,
                            $estado_producto
                        )) {
                            $insertados++;
                        } else {
                            $errores++;
                            $erroresDetalle[] = "Fila $filaNumero: error al insertar producto código '$codigo_productos'";
                        }
                    }
                } catch (Exception $e) {
                    $errores++;
                    $erroresDetalle[] = "Fila $filaNumero: " . $e->getMessage();
                }
            }

            // Crear parámetros para la URL
            $params = [
                'importados' => $insertados,
                'actualizados' => $actualizados,
                'errores' => $errores,
                'success' => 1,
                'filas_vacias' => $filasVacias,
                'total_procesadas' => count($datos),
                'precios_actualizados' => $preciosActualizados
            ];

            // Agregar algunos errores (máximo 5) si los hay
            if (!empty($erroresDetalle)) {
                $params['errores_detalle'] = implode('|', array_slice($erroresDetalle, 0, 5));
            }

            $queryString = http_build_query($params);
            header("Location: ../views/Subir_excel_producto.php?$queryString");
            exit;
        } catch (Exception $e) {
            header("Location: ../views/Subir_excel_producto.php?error=" . urlencode("Error: " . $e->getMessage()));
            exit;
        }
    } else {
        header("Location: ../views/Subir_excel_producto.php?error=" . urlencode("Error al procesar archivo"));
        exit;
    }
} else {
    header("Location: ../views/Subir_excel_producto.php?error=" . urlencode("Solicitud inválida"));
    exit;
}
