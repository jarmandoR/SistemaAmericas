<?php
require_once "../models/database.php";
require_once "../models/PedidoModel.php";

// Habilitar errores para debugging (eliminar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar que se recibieron datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['productos'])) {
    header('Location: catalogo.php?error=sin_productos');
    exit;
}

try {
    // Obtener datos del formulario
    $productos = $_POST['productos'] ?? [];
    $totalGeneral = floatval($_POST['total_general'] ?? 0);
    $observaciones = $_POST['observaciones'] ?? '';





    // Validar que hay productos
    if (empty($productos)) {
        throw new Exception('No se recibieron productos');
    }

    // Validar estructura de productos
    foreach ($productos as $index => $producto) {
        if (
            empty($producto['id']) || empty($producto['nombre']) ||
            empty($producto['tipo']) || empty($producto['cantidad'])
        ) {
            throw new Exception("Producto en posición $index tiene datos incompletos");
        }
    }

    // Datos del cliente simplificados (sin formulario)
    $numCliente = $_POST['numCliente'] ?? 0;
    $ped_sede = $_POST['ped_sede'] ?? null; // Dirección del cliente seleccionado
    $datosCliente = [
        'nombre' => 'Cliente Web',
        'email' => 'pedido@multilicores.com'
    ];

    // Instanciar modelo de pedidos
    $pedidoModel = new Pedido();

    // Crear el pedido
    $idPedido = $pedidoModel->crearPedido($datosCliente, $productos, $totalGeneral, $numCliente, $observaciones, $ped_sede);

    if ($idPedido) {
        // Pedido creado exitosamente
        $mensaje = "Pedido creado exitosamente";
        $tipo = "success";
        $limpiarCarrito = true;
        $totalPedido=number_format($totalGeneral, 0, ',', '.');
        $resumenPedido= "";
        $dProducto= "";
        foreach ($productos as $producto): 
            $dProducto .= $producto['nombre']."\n".$producto['tipo'];
            $dProducto .= "{$producto['tipo']}";
            $dProducto .= "{$producto['cantidad']}";
            $dProducto .= "{$producto['precio_unitario']}";
            $dProducto .= "{$producto['subtotal']}";
        endforeach;
        
        $resumenPedido .= "Valor total pedido $".$totalPedido;
                                     
        $respuesta = $pedidoModel->enviarConfirmacion($idPedido, "$resumenPedido ", "", "$numCliente","pedido_recepcionado");
        error_log(print_r($respuesta, true));
    } else {
        throw new Exception('Error al crear el pedido');
    }
} catch (Exception $e) {
    error_log("Error en procesar_pedido.php: " . $e->getMessage());
    $mensaje = "Error al procesar el pedido: " . $e->getMessage();
    $tipo = "error";
    $limpiarCarrito = false;
    $idPedido = null;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del Pedido - Multilicores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-animation {
            animation: successPulse 1s ease-in-out;
        }

        @keyframes successPulse {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
        }

        .card-header {
            border: none;
        }

        .btn {
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <?php if ($tipo === 'success'): ?>
                    <div class="card border-0 shadow-lg success-animation">
                        <div class="card-header bg-success text-white text-center py-4">
                            <h3 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                ¡Pedido Enviado!
                            </h3>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <div class="text-success mb-3">
                                    <i class="fas fa-shopping-cart fa-4x"></i>
                                </div>
                                <h4 class="text-success mb-3">Pedido #<?php echo $idPedido; ?></h4>
                                <p class="lead text-muted"><?php echo $mensaje; ?></p>
                                <div class="bg-light rounded p-3 d-inline-block">
                                    <h5 class="mb-0 text-success">
                                        <i class="fas fa-dollar-sign me-1"></i>
                                        Total: $<?php echo number_format($totalGeneral, 0, ',', '.'); ?> COP
                                    </h5>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-block">
                                <a href="catalogo.php" class="btn btn-success btn-lg px-4 me-md-2">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Volver al Catálogo
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-danger text-white text-center py-4">
                            <h3 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al Procesar
                            </h3>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <div class="text-danger mb-3">
                                    <i class="fas fa-times-circle fa-4x"></i>
                                </div>
                                <h4 class="text-danger mb-3">No se pudo procesar el pedido</h4>
                                <p class="lead text-muted"><?php echo $mensaje; ?></p>
                            </div>

                            <div class="d-grid gap-2 d-md-block">
                                <a href="catalogo.php?idCli=<?php echo $numCliente; ?>" class="btn btn-secondary btn-lg px-4 me-md-2">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Volver al Catálogo
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="history.back()">
                                    <i class="fas fa-undo me-1"></i>
                                    Intentar de Nuevo
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen de productos -->
        <?php if (!empty($productos) && $tipo === 'success'): ?>
            <div class="row justify-content-center mt-4">
                <div class="col-md-10 col-lg-8">
                    <div class="card border-0 shadow">
                        <div class="card-header bg-light border-0">
                            <h5 class="mb-0 text-center">
                                <i class="fas fa-list me-2 text-primary"></i>
                                Resumen del Pedido
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <!-- Vista Desktop -->
                            <div class="d-none d-md-block">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Producto</th>
                                                <th class="border-0">Tipo</th>
                                                <th class="border-0">Cantidad</th>
                                                <th class="border-0">Precio Unit.</th>
                                                <th class="border-0">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($productos as $producto): ?>
                                                <tr>
                                                    <td class="align-middle fw-semibold">
                                                        <?php echo htmlspecialchars($producto['nombre'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge bg-<?php echo ($producto['tipo'] ?? '') === 'paca' ? 'primary' : 'secondary'; ?>">
                                                            <?php echo ucfirst($producto['tipo'] ?? 'N/A'); ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle fw-bold">
                                                        <?php echo intval($producto['cantidad'] ?? 0); ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        $<?php echo number_format(floatval($producto['precio_unitario'] ?? 0), 0, ',', '.'); ?>
                                                    </td>
                                                    <td class="align-middle fw-bold text-success">
                                                        $<?php echo number_format(floatval($producto['subtotal'] ?? 0), 0, ',', '.'); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="4" class="text-end border-0">Total General:</th>
                                                <th class="border-0 text-success">
                                                    $<?php echo number_format($totalGeneral, 0, ',', '.'); ?> COP
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Vista Mobile -->
                            <div class="d-md-none p-3">
                                <?php foreach ($productos as $producto): ?>
                                    <div class="card mb-3 border-light">
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2 fw-bold">
                                                <?php echo htmlspecialchars($producto['nombre'] ?? 'N/A'); ?>
                                            </h6>
                                            <div class="row g-2 small">
                                                <div class="col-6">
                                                    <span class="badge bg-<?php echo ($producto['tipo'] ?? '') === 'paca' ? 'primary' : 'secondary'; ?>">
                                                        <?php echo ucfirst($producto['tipo'] ?? 'N/A'); ?>
                                                    </span>
                                                </div>
                                                <div class="col-6 text-end fw-bold">
                                                    Cant: <?php echo intval($producto['cantidad'] ?? 0); ?>
                                                </div>
                                                <div class="col-6 text-muted">
                                                    $<?php echo number_format(floatval($producto['precio_unitario'] ?? 0), 0, ',', '.'); ?>
                                                </div>
                                                <div class="col-6 text-end fw-bold text-success">
                                                    $<?php echo number_format(floatval($producto['subtotal'] ?? 0), 0, ',', '.'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Total para mobile -->
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Total General:</h5>
                                        <h4 class="mb-0 text-success fw-bold">
                                            $<?php echo number_format($totalGeneral, 0, ',', '.'); ?> COP
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <!-- observacion -->
                            <div>
                                <?php if (!empty($observaciones)): ?>
                                    <div class="border-top p-3 bg-light">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <i class="fas fa-comment-dots text-primary fa-lg"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2 text-primary">Observaciones</h6>
                                                <div class="bg-white rounded p-3 shadow-sm">
                                                    <p class="mb-0 text-dark">
                                                        <?php echo nl2br(htmlspecialchars($observaciones)); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
  <!-- Toast -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="contadorToast" class="toast show align-items-center text-bg-dark border-0">
      <div class="d-flex">
        <div class="toast-body">
          🔄 La página se redirigirá en <span id="segundos">5</span> segundos...
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($limpiarCarrito): ?>
        <script>
            // Limpiar carrito después de pedido exitoso
            try {
                localStorage.removeItem('carritoMultilicores');
                localStorage.removeItem('carritoMultilicores_timestamp');
                sessionStorage.removeItem('observacionesPedido');
                if (typeof observacionesGlobales !== 'undefined') {
                    observacionesGlobales = "";
                }
                console.log('Carrito limpiado después de pedido exitoso');


                // Mostrar notificación de éxito
                if (typeof window !== 'undefined') {
                    setTimeout(() => {
                        const successCard = document.querySelector('.success-animation');
                        if (successCard) {
                            successCard.style.transform = 'scale(1.02)';
                            setTimeout(() => {
                                successCard.style.transform = 'scale(1)';
                            }, 200);
                        }
                    }, 100);
                }
            } catch (e) {
                console.error('Error al limpiar carrito:', e);
            }
            let segundos = 10;
            let spanSegundos = document.getElementById("segundos");
            let redireccionAutomatica = false; // bandera para saber si la salida fue intencional

            let intervalo = setInterval(function() {
                segundos--;
                spanSegundos.textContent = segundos;

                if (segundos <= 0) {
                    let numCliente = <?php echo json_encode($numCliente); ?>;
                    console.log("Número de cliente:", numCliente);
                    clearInterval(intervalo);

                    redireccionAutomatica = true; // ✅ marcamos que fue nuestra redirección
                    window.location.href = "https://multilicoreschapinero.com/sistema/views/categorias.php?idCli=" + numCliente;
                }
            }, 1000);

            window.addEventListener("beforeunload", function (e) {
                if (!redireccionAutomatica) { 
                    // ❌ Solo mostrar la alerta si NO fue redirección automática
                    e.preventDefault();
                    e.returnValue = ""; // alerta estándar
                }
            });
        </script>

    <?php endif; ?>
</body>

</html>