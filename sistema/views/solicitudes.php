<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery UI -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<!-- Emoji Button (si lo necesitas) -->
<script type="module" src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.2/dist/index.min.js"></script>

<script>
$(function() {
    $("#cliente").autocomplete({
        source: "buscar_cliente.php",
        minLength: 2,
        select: function(event, ui) {
            $("#cliente").val(ui.item.label);
            $("#cliente_id").val(ui.item.value);
            return false;
        }
    });
});
</script>

<?php
include_once "header.php";
require_once "../models/database.php";
require_once "../models/solicitudModel.php";

// Calcular fechas por defecto
$hoy = date("Y-m-d");
$ayer = date("Y-m-d", strtotime("-1 day"));


// Si vienen fechas por GET, las usamos; si no, asignamos los valores por defecto
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : $ayer;
$fecha_fin    = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : $hoy;
$cliente      = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';
// Instanciar la clase Solicitud
$solicitudModel = new solicitud();

// Procesamiento del formulario de aceptar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'aceptar_pedido') {
    $pedido_id = $_POST['pedido_id'] ?? '';
    $numCliente = $_POST['numCliente'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    // if ($Observaciones!="") {
    //     $Observaciones="Observaciones: ".$observaciones;
    // }

    if (!empty($pedido_id)) {
        try {
            if ($solicitudModel->aceptarPedido($pedido_id)) {
                $mensaje_exito = "El pedido ha sido aceptado exitosamente";
                $plantilla="recibido";

                $respuesta = $solicitudModel->enviarPromo($pedido_id, "$observaciones", "", "$numCliente", $plantilla);
                error_log(print_r($respuesta, true));
                echo json_encode($respuesta);
                exit;
            } else {
                $errores[] = "Error al actualizar el pedido";
            }
        } catch (Exception $e) {
            $errores[] = "Error: " . $e->getMessage();
        }
    } else {
        $errores[] = "ID de pedido requerido";
    }
}

// Procesamiento del formulario de rechazar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rechazar_pedido') {
    $pedido_id = $_POST['pedido_id'] ?? '';
    $numCliente = $_POST['numCliente'] ?? '';
    $observaciones = $_POST['motivo_rechazo'] ?? '';
    // if ($Observaciones!="") {
    //     $Observaciones="Observaciones: ".$observaciones;
    // }
    
    if (!empty($pedido_id)) {
        try {
            if ($solicitudModel->rechazarPedido($pedido_id)) {
                $mensaje_exito = "El pedido ha sido aceptado exitosamente";
                $plantilla="rechazado";

                $respuesta = $solicitudModel->enviarPromo($pedido_id, "$observaciones", "", "$numCliente", $plantilla);
                error_log(print_r($respuesta, true));
                echo json_encode($respuesta);
                exit;
            } else {
                $errores[] = "Error al rechazar el pedido";
            }
        } catch (Exception $e) {
            $errores[] = "Error: " . $e->getMessage();
        }
    } else {
        $errores[] = "ID de pedido requerido";
    }
}

// Procesamiento del formulario de enviar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enviar_pedido') {
    $pedido_id = $_POST['pedido_id'] ?? '';
    $numCliente = $_POST['numCliente'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    // if ($Observaciones!="") {
    //     $Observaciones="Observaciones: ".$observaciones;
    // }

    if (!empty($pedido_id)) {
        $estado = "enviado";
        try {
            if ($solicitudModel->cambiarEstadoPedido($pedido_id,$estado)) {
                $mensaje_exito = "El pedido ha sido aceptado exitosamente";
                $plantilla="en_camino";

                $respuesta = $solicitudModel->enviarPromo($pedido_id, "$pedido_id", "", "$numCliente", $plantilla);
                error_log(print_r($respuesta, true));
                echo json_encode($respuesta);
                exit;
            } else {
                $errores[] = "Error al actualizar el pedido";
            }
        } catch (Exception $e) {
            $errores[] = "Error: " . $e->getMessage();
        }
    } else {
        $errores[] = "ID de pedido requerido";
    }
}

// Procesamiento del formulario de finalizar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalizar_pedido') {
    $pedido_id = $_POST['pedido_id'] ?? '';
    $numCliente = $_POST['numCliente'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $tipo_pago = $_POST['tipo_pago'] ?? '';
    //     $Observaciones="Observaciones: ".$observaciones;
    // }

    if (!empty($pedido_id)) {
        try {
            if ($tipo_pago=="contado") {
                $plantilla="pago_contado";
                $estado = "finalizaContado";
            }else{
                $plantilla="pago_credito";
                $estado = "finalizaCredito";
            }
            if ($solicitudModel->cambiarEstadoPedido($pedido_id,$estado)) {
                $mensaje_exito = "El pedido ha sido Finalizado";


               
                

                $respuesta = $solicitudModel->enviarPromo($pedido_id, "$pedido_id", "", "$numCliente", $plantilla);
                error_log(print_r($respuesta, true));
                echo json_encode($respuesta);
                exit;
            } else {
                $errores[] = "Error al actualizar el pedido";
            }
        } catch (Exception $e) {
            $errores[] = "Error: " . $e->getMessage();
        }
    } else {
        $errores[] = "ID de pedido requerido";
    }
}

// Obtener pedidos usando la clase
$pedidos_pendientes = $solicitudModel->obtenerPedidosPendientes($fecha_inicio,$fecha_fin);
$pedidos_aceptados = $solicitudModel->obtenerPedidosAceptados($fecha_inicio,$fecha_fin);
$pedidos_rechazados = $solicitudModel->obtenerPedidosRechazados($fecha_inicio,$fecha_fin);

?>

<div class="container-fluid px-4 py-3">
    <!-- Header Moderno -->
    <div class="page-header">
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-shopping-cart fa-2x me-3"></i>
            <div>
                <h1 class="mb-0 fw-bold">Recepción de Pedidos</h1>
                <p class="mb-0 opacity-90">Gestione las solicitudes de pedidos de los clientes</p>
            </div>
        </div>
    </div>

    <!-- Alertas Modernas -->
    <div id="alert-container"></div>
    <?php if (isset($mensaje_exito)): ?>
    <div class="alert alert-modern alert-success-modern d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <div>
            <strong>¡Perfecto!</strong> <?php echo $mensaje_exito; ?>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
    <div class="alert alert-modern alert-danger-modern mb-4" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
            <div>
                <strong>Errores encontrados:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filtros Modernos -->
    <div class="modern-card mb-4">
        <div class="p-4 border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-filter text-primary me-2"></i>
                <h5 class="mb-0 fw-semibold">Filtros</h5>
            </div>
        </div>
        <div class="p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label fw-medium">Fecha Inicial</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                           value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label fw-medium">Fecha Final</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                           value="<?php echo $fecha_fin; ?>">
                </div>
                <div class="col-md-3">
                    <label for="cliente" class="form-label fw-medium">Cliente</label>
                    <input type="text" class="form-control" id="cliente" name="cliente"
                        placeholder="Escriba nombre o identificación"
                        value="<?php echo htmlspecialchars($cliente); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-modern btn-primary-modern w-100" onclick="filtrarPorFecha()">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <button type="button" class="btn btn-modern btn-outline-modern w-100" onclick="limpiarFiltros()">
                        <i class="fas fa-undo me-2"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Moderna -->
    <div class="modern-card">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-list text-primary me-2"></i>
                        <h5 class="mb-0 fw-semibold">Solicitudes de Pedidos</h5>
                    </div>
                    <div class="js-pedidos-resumen pedidos-resumen">
                        <button type="button" class="badge bg-info indicador-pedido" data-estado="" aria-pressed="true" title="Ver todos los pedidos">
                            Total: <?php echo count($pedidos_pendientes) + count($pedidos_aceptados) + count($pedidos_rechazados); ?>
                        </button>
                        <button type="button" class="badge bg-warning ms-2 indicador-pedido" data-estado="Pendiente" aria-pressed="false" title="Ver pedidos pendientes">
                            Pendientes: <?php echo count($pedidos_pendientes); ?>
                        </button>
                        <button type="button" class="badge bg-success ms-2 indicador-pedido" data-estado="Aceptado" aria-pressed="false" title="Ver pedidos aceptados">
                            Aceptados: <?php echo count($pedidos_aceptados); ?>
                        </button>
                        <button type="button" class="badge bg-danger ms-2 indicador-pedido" data-estado="Rechazado" aria-pressed="false" title="Ver pedidos rechazados">
                            Rechazados: <?php echo count($pedidos_rechazados); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-modern mb-0" id="tabla-pedidos">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>N° Factura</th>
                            <th>Cliente</th>
                            <th>Fecha y Hora</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Cliente/Sede</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                                <!-- Mostrar todos los pedidos -->
                                <?php 
                                // Combinar todos los pedidos
                                $todos_pedidos = array_merge($pedidos_pendientes, $pedidos_aceptados, $pedidos_rechazados);
                                
                                if (!empty($todos_pedidos)):
                                    foreach ($todos_pedidos as $pedido):
                                        // Determinar el estado y color
                                        $estado_text = '';
                                        $badge_class = '';
                                        if (in_array($pedido['id_pedido'], array_column($pedidos_pendientes, 'id_pedido'))) {
                                            $estado_text = 'Pendiente';
                                            $badge_class = 'bg-warning';
                                        } elseif (in_array($pedido['id_pedido'], array_column($pedidos_aceptados, 'id_pedido'))) {
                                            $estado_text = 'Aceptado';
                                            $badge_class = 'bg-success';
                                        } else {
                                            $estado_text = 'Rechazado';
                                            $badge_class = 'bg-danger';
                                        }
                                ?>
                                    <tr>
                                        <td><span class="fw-medium"><?php echo $pedido['id_pedido']; ?></span></td>
                                        <td><?php echo $pedido['ped_numfac']; ?></td>
                                        <td><?php echo $pedido['ped_cliente']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['ped_fecha'])); ?></td>
                                        <td><span class="fw-medium">$<?php echo number_format($pedido['ped_total'], 2); ?></span></td>
                                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo $estado_text; ?></span></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($pedido['nombre_bar'] ?? ''); ?></strong>
                                            <?php if (!empty($pedido['ped_sede'])): ?>
                                                <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($pedido['ped_sede']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary ver-productos" data-pedido="<?php echo $pedido['id_pedido']; ?>" title="Ver productos">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($estado_text === 'Pendiente'): ?>
                                                    <button class="btn btn-success aceptar-pedido" data-pedido="<?php echo $pedido['id_pedido']; ?>" data-numcliente="<?php echo htmlspecialchars($pedido['ped_numCliente']); ?>" title="Aceptar">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger rechazar-pedido" data-pedido="<?php echo $pedido['id_pedido']; ?>" data-numcliente="<?php echo htmlspecialchars($pedido['ped_numCliente']); ?>" title="Rechazar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php elseif ($estado_text === 'Aceptado'): ?>
                                                    <?php if ($pedido['ped_estado']=="aceptado"): ?>
                                                        <button class="btn btn-primary enviar-pedido" data-pedido="<?php echo $pedido['id_pedido']; ?>" data-numcliente="<?php echo htmlspecialchars($pedido['ped_numCliente']); ?>" title="Enviar">
                                                            <i class="fas fa-truck"></i> Enviar
                                                        </button>
                                                    <?php elseif ($pedido['ped_estado']=="enviado"): ?>
                                                        <button class="btn btn-warning finalizar-pedido" data-pedido="<?php echo $pedido['id_pedido']; ?>" data-numcliente="<?php echo htmlspecialchars($pedido['ped_numCliente']); ?>" title="Finalizar">
                                                            <i class="fas fa-check-double"></i> Finalizar
                                                        </button>
                                                    <?php elseif ($pedido['ped_estado']=="finalizaContado" or $pedido['ped_estado']=="finalizaCredito"): ?>
                                                        <button class="btn btn-success" disabled title="Procesado">
                                                            <i class="fas fa-check-circle"></i> Procesado
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="fas fa-ban"></i> Rechazado</span>
                                                <?php endif; ?>
                                            </div>
                                            <input type="hidden" id="numCliente<?php echo $pedido['id_pedido']; ?>" data-numcliente="<?php echo $pedido['ped_numCliente']; ?>" />
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
</div>

<!-- Modal para ver productos del pedido -->
<div class="modal fade" id="productosModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Productos del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="productos-content">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Cargando productos...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para rechazar pedido -->
<div class="modal fade" id="rechazarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechazar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-rechazar" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="rechazar_pedido">
                    <input type="hidden" name="pedido_id" id="pedido_id_rechazar">
                    <input type="hidden" name="numCliente" id="numClientePedRechaza">
                    <div class="form-group">
                        <label>Motivo del rechazo:</label>
                        <textarea name="motivo_rechazo" class="form-control" rows="3" required
                                placeholder="Explique por qué se rechaza este pedido..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Esta acción cambiará el estado del pedido a "Rechazado".
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Rechazar Pedido
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para aceptar pedido -->
<div class="modal fade" id="aceptarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aceptar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-aceptar" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="aceptar_pedido">
                    <input type="hidden" name="pedido_id" id="pedido_id_aceptar">
                    <input type="hidden" name="numCliente" id="numClientePedAcepta">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Confirmar:</strong> ¿Está seguro de que desea aceptar este pedido?
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones (opcional):</label>
                        <textarea name="observaciones" class="form-control" rows="2" 
                                placeholder="Comentarios adicionales sobre el pedido..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Aceptación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal para enviar pedido -->
<div class="modal fade" id="enviarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-enviar" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="enviar_pedido">
                    <input type="hidden" name="pedido_id" id="pedido_id_enviar">
                    <input type="hidden" name="numCliente" id="numClientePedEnviar">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Confirmar:</strong> ¿Está seguro de que el pedido será enviado?
                    </div>
                    
                    <!-- <div class="form-group">
                        <label>Observaciones (opcional):</label>
                        <textarea name="observaciones" class="form-control" rows="2" 
                                placeholder="Comentarios adicionales sobre el pedido..."></textarea>
                    </div> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Si
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para finalizar pedido -->
<div class="modal fade" id="finalizarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Finalizar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-finalizar" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="finalizar_pedido">
                    <input type="hidden" name="pedido_id" id="pedido_id_finalizar">
                    <input type="hidden" name="numCliente" id="numClientePedFinalizar">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Confirmar:</strong> ¿El pedido será finalizado con modalidad de pago?
                    </div>
                    
                    <!-- <div class="form-group">
                        <label>Observaciones (opcional):</label>
                        <textarea name="observaciones" class="form-control" rows="2" 
                                placeholder="Comentarios adicionales sobre el pedido..."></textarea>
                    </div> -->
                </div>
                    <!-- Botón Crédito -->
                    <button type="submit" name="tipo_pago" value="credito" class="btn btn-success">
                        <i class="fas fa-check"></i> Finalizar como crédito
                    </button>

                    <!-- Botón Contado -->
                    <button type="submit" name="tipo_pago" value="contado" class="btn btn-warning">
                        <i class="fas fa-check"></i> Finalizar como contado
                    </button>
            </form>
        </div>
    </div>
</div>

<script>
let dataTablesCargando = false;
let filtroEstadoPedidos = '';
const COLUMNA_ESTADO_PEDIDOS = 5;

function mostrarModal(id) {
    const modalElement = document.getElementById(id);

    if (window.jQuery && $.fn.modal) {
        $('#' + id).modal('show');
        return;
    }

    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
}

function ocultarModales() {
    if (window.jQuery && $.fn.modal) {
        $('.modal.show').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        return;
    }

    if (window.bootstrap && bootstrap.Modal) {
        document.querySelectorAll('.modal.show').forEach(modal => {
            const instance = bootstrap.Modal.getInstance(modal);
            if (instance) {
                instance.hide();
            }
        });
    }
}

document.addEventListener('click', manejarAccionesPedido);
document.addEventListener('click', manejarFiltroResumenPedidos);

document.addEventListener('DOMContentLoaded', function() {
    registrarEnvioFormulario('form-aceptar');
    registrarEnvioFormulario('form-rechazar');
    registrarEnvioFormulario('form-enviar');
    registrarEnvioFormulario('form-finalizar', function(formData, event) {
        const boton = event.submitter;
        const tipoPago = boton ? boton.value : '';

        if (tipoPago) {
            formData.append('tipo_pago', tipoPago);
        }
    });
});

function registrarEnvioFormulario(formId, prepararFormData) {
    const form = document.getElementById(formId);

    if (!form) {
        return;
    }

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        const submitButton = event.submitter || this.querySelector('button[type="submit"]');
        const originalButtonHtml = submitButton ? submitButton.innerHTML : '';

        if (typeof prepararFormData === 'function') {
            prepararFormData(formData, event);
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        }

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text().then(text => {
            if (!response.ok) {
                throw new Error('Error al procesar el pedido');
            }

            if (text.trim()) {
                try {
                    const data = JSON.parse(text);
                    if (data.success === false) {
                        throw new Error(data.message || 'Error al procesar el pedido');
                    }
                } catch (error) {
                    if (text.trim().startsWith('{')) {
                        throw error;
                    }
                }
            }

            ocultarModales();
            return refrescarTablaPedidos();
        }))
        .then(() => {
            mostrarAlertaTabla('success', 'Solicitud actualizada correctamente');
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlertaTabla('error', error.message || 'Error de conexión');
        })
        .finally(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;
            }
        });
    });
}

function mostrarAlertaTabla(type, message) {
    const container = document.getElementById('alert-container');

    if (!container) {
        return;
    }

    const alertClass = type === 'success' ? 'alert-success-modern' : 'alert-danger-modern';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

    container.innerHTML = `
        <div class="alert alert-modern ${alertClass} d-flex align-items-center mb-4" role="alert">
            <i class="fas ${icon} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" data-dismiss="alert" aria-label="Cerrar"></button>
        </div>`;

    setTimeout(() => {
        container.innerHTML = '';
    }, 3500);
}

function obtenerEstadoDataTable() {
    if (!window.jQuery || !$.fn.DataTable || !$.fn.DataTable.isDataTable('#tabla-pedidos')) {
        return { search: '', page: 0, length: 50, estado: filtroEstadoPedidos };
    }

    const tabla = $('#tabla-pedidos').DataTable();

    return {
        search: tabla.search(),
        page: filtroEstadoPedidos ? 0 : tabla.page(),
        length: tabla.page.len(),
        estado: filtroEstadoPedidos
    };
}

function refrescarTablaPedidos() {
    const estadoTabla = obtenerEstadoDataTable();

    return fetch(window.location.href, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('No se pudo refrescar la tabla');
        }
        return response.text();
    })
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nuevoTbody = doc.querySelector('#tabla-pedidos tbody');
        const nuevoResumen = doc.querySelector('.js-pedidos-resumen');
        const tbodyActual = document.querySelector('#tabla-pedidos tbody');
        const resumenActual = document.querySelector('.js-pedidos-resumen');

        if (!nuevoTbody || !tbodyActual) {
            throw new Error('No se pudo leer la tabla actualizada');
        }

        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#tabla-pedidos')) {
            $('#tabla-pedidos').DataTable().destroy();
        }

        tbodyActual.innerHTML = nuevoTbody.innerHTML;

        if (nuevoResumen && resumenActual) {
            resumenActual.innerHTML = nuevoResumen.innerHTML;
        }

        inicializarTablaPedidos(estadoTabla);
    });
}

function manejarFiltroResumenPedidos(event) {
    const indicador = event.target.closest('.indicador-pedido');

    if (!indicador) {
        return;
    }

    filtrarPedidosPorEstado(indicador.getAttribute('data-estado') || '');
}

function normalizarFiltroEstadoPedidos(estado) {
    return ['Pendiente', 'Aceptado', 'Rechazado'].includes(estado) ? estado : '';
}

function filtrarPedidosPorEstado(estado) {
    filtroEstadoPedidos = normalizarFiltroEstadoPedidos(estado);

    if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#tabla-pedidos')) {
        const tabla = $('#tabla-pedidos').DataTable();
        aplicarFiltroEstadoPedidos(tabla, filtroEstadoPedidos, true);
    }

    actualizarIndicadoresPedidos();
}

function aplicarFiltroEstadoPedidos(tabla, estado, reiniciarPagina) {
    const filtro = estado ? '^' + $.fn.dataTable.util.escapeRegex(estado) + '$' : '';

    tabla.column(COLUMNA_ESTADO_PEDIDOS).search(filtro, true, false);

    if (reiniciarPagina) {
        tabla.page('first').draw(false);
        return;
    }

    tabla.draw(false);
}

function actualizarIndicadoresPedidos() {
    document.querySelectorAll('.indicador-pedido').forEach(indicador => {
        const activo = (indicador.getAttribute('data-estado') || '') === filtroEstadoPedidos;
        indicador.classList.toggle('active', activo);
        indicador.setAttribute('aria-pressed', activo ? 'true' : 'false');
    });
}

function obtenerNumCliente(button) {
    const pedidoId = button.getAttribute('data-pedido');
    const hiddenNumCliente = document.getElementById('numCliente' + pedidoId);

    return button.getAttribute('data-numcliente') || (hiddenNumCliente ? hiddenNumCliente.dataset.numcliente : '');
}

function manejarAccionesPedido(event) {
    const button = event.target.closest('.ver-productos, .aceptar-pedido, .enviar-pedido, .finalizar-pedido, .rechazar-pedido');

    if (!button) {
        return;
    }

    const pedidoId = button.getAttribute('data-pedido');
    const valorNumCliente = obtenerNumCliente(button);

    if (button.classList.contains('ver-productos')) {
        cargarProductosPedido(pedidoId);
        return;
    }

    if (button.classList.contains('aceptar-pedido')) {
        document.getElementById('pedido_id_aceptar').value = pedidoId;
        document.getElementById('numClientePedAcepta').value = valorNumCliente;
        mostrarModal('aceptarModal');
        return;
    }

    if (button.classList.contains('enviar-pedido')) {
        document.getElementById('pedido_id_enviar').value = pedidoId;
        document.getElementById('numClientePedEnviar').value = valorNumCliente;
        mostrarModal('enviarModal');
        return;
    }

    if (button.classList.contains('finalizar-pedido')) {
        document.getElementById('pedido_id_finalizar').value = pedidoId;
        document.getElementById('numClientePedFinalizar').value = valorNumCliente;
        mostrarModal('finalizarModal');
        return;
    }

    if (button.classList.contains('rechazar-pedido')) {
        document.getElementById('pedido_id_rechazar').value = pedidoId;
        document.getElementById('numClientePedRechaza').value = valorNumCliente;
        mostrarModal('rechazarModal');
    }
}


function cargarProductosPedido(pedidoId) {
    const content = document.getElementById('productos-content');
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando productos...</div>';
    
    fetch('../controllers/pedidoController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=obtener_productos&pedido_id=${pedidoId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.productos.length > 0) {
            let html = '<div class="table-responsive">';
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Codigo</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>';
            html += '<tbody>';
            
            data.productos.forEach(producto => {
                html += `<tr>
                    <td>${producto.codigo_productos}</td>
                    <td>${producto.nombre_producto}</td>
                    <td class="text-center">${producto.tipo_producto}</td>
                    <td class="text-center">${producto.cantidad}</td>
                    <td class="text-right">$${parseFloat(producto.precio_unitario).toFixed(2)}</td>
                    <td class="text-right">$${parseFloat(producto.subtotal).toFixed(2)}</td>
                    
                </tr>`;
            });
            if (data.productos[0].ped_observacion) {
                    html += `
                        <tr class="table-active">
                            <td colspan="6" class="text-muted">
                                <i class="fas fa-comment-dots"></i> 
                                <em>${data.productos[0].ped_observacion}</em>
                            </td>
                        </tr>
                    `;
                }
            
            html += '</tbody></table></div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = '<div class="alert alert-warning">No se encontraron productos para este pedido.</div>';
        }
        
        mostrarModal('productosModal');
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="alert alert-danger">Error al cargar los productos.</div>';
        mostrarModal('productosModal');
    });
}
function filtrarPorFecha() {
    const params = new URLSearchParams();
    const inicio = document.getElementById("fecha_inicio").value;
    const fin = document.getElementById("fecha_fin").value;
    const cliente = document.getElementById("cliente").value.trim();

    if (inicio) {
        params.set('fecha_inicio', inicio);
    }
    if (fin) {
        params.set('fecha_fin', fin);
    }
    if (cliente) {
        params.set('cliente', cliente);
    }

    let url = window.location.pathname + "?" + params.toString();
    window.location.href = url;
}

function limpiarFiltros() {
    window.location.href = window.location.pathname;
}

$(function() {
    $("#cliente").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "../controllers/buscar_clientes.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2, // empieza a buscar desde 2 caracteres
        select: function(event, ui) {
            // cuando selecciona un cliente del autocompletado
            $("#cliente").val(ui.item.label);
            return false;
        }
    });
});

// Inicializar DataTable
document.addEventListener('DOMContentLoaded', function() {
    inicializarTablaPedidos();
});

function inicializarTablaPedidos(options) {
    options = options || {};
    filtroEstadoPedidos = normalizarFiltroEstadoPedidos(
        options.estado !== undefined ? options.estado : filtroEstadoPedidos
    );

    if (!window.jQuery) {
        return;
    }

    if (!$.fn.DataTable) {
        cargarDataTables(function() {
            inicializarTablaPedidos(options);
        });
        return;
    }

    if ($.fn.DataTable.isDataTable('#tabla-pedidos')) {
        $('#tabla-pedidos').DataTable().destroy();
    }

    const tabla = $('#tabla-pedidos').DataTable({
        pageLength: options.length || 50,
        lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'Todos']],
        order: [[0, 'desc']],
        search: {
            search: options.search !== undefined ? options.search : <?php echo json_encode($cliente); ?>
        },
        columnDefs: [
            { orderable: false, targets: [7, 8] }
        ],
        language: {
            decimal: '',
            emptyTable: 'No hay pedidos registrados',
            info: '_START_ - _END_ de _TOTAL_ pedidos',
            infoEmpty: '0 pedidos',
            infoFiltered: '(filtrado de _MAX_ pedidos en total)',
            lengthMenu: 'Mostrar _MENU_ pedidos',
            loadingRecords: 'Cargando...',
            processing: 'Procesando...',
            search: 'Buscar:',
            zeroRecords: 'No se encontraron pedidos',
            paginate: {
                first: '<i class="fas fa-angles-left" aria-hidden="true"></i>',
                last: '<i class="fas fa-angles-right" aria-hidden="true"></i>',
                next: '<i class="fas fa-chevron-right" aria-hidden="true"></i>',
                previous: '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
            }
        }
    });

    if (filtroEstadoPedidos) {
        aplicarFiltroEstadoPedidos(tabla, filtroEstadoPedidos, false);
    }

    if (options.page !== undefined) {
        tabla.page(options.page).draw('page');
    }

    actualizarIndicadoresPedidos();
}

function cargarDataTables(callback) {
    if (dataTablesCargando) {
        return;
    }

    dataTablesCargando = true;
    cargarScript('https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js', function() {
        cargarScript('https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js', function() {
            dataTablesCargando = false;
            callback();
        });
    });
}

function cargarScript(src, callback) {
    const script = document.createElement('script');
    script.src = src;
    script.onload = callback;
    script.onerror = function() {
        dataTablesCargando = false;
        console.error('Error cargando: ' + src);
    };
    document.body.appendChild(script);
}

</script>

<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --success-color: #059669;
    --danger-color: #dc2626;
    --warning-color: #d97706;
    --light-bg: #f8fafc;
    --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

body {
    background-color: var(--light-bg);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.modern-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.modern-card:hover {
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.page-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.form-control, .form-select {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
}

.btn-modern {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
}

.btn-primary-modern {
    background: var(--primary-color);
    color: white;
}

.btn-primary-modern:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.btn-outline-modern {
    background: transparent;
    border: 1px solid #d1d5db;
    color: var(--secondary-color);
}

.btn-outline-modern:hover {
    background: #f8fafc;
    border-color: var(--primary-color);
}

.pedidos-resumen .indicador-pedido {
    border: 0;
    cursor: pointer;
    font: inherit;
    line-height: 1;
    padding: 0.45em 0.65em;
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
}

.pedidos-resumen .indicador-pedido:hover,
.pedidos-resumen .indicador-pedido.active {
    box-shadow: 0 0 0 3px rgb(37 99 235 / 0.18);
    transform: translateY(-1px);
}

.pedidos-resumen .indicador-pedido:not(.active) {
    opacity: 0.78;
}

.table-modern {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}

.dataTables_wrapper {
    padding: 1rem;
}

.dataTables_wrapper .dataTables_info {
    color: #64748b;
    font-size: 0.875rem;
    padding-top: 0.65rem;
}

.dataTables_wrapper .dataTables_paginate {
    padding-top: 0.45rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.page-item {
    margin: 0 0.125rem;
}

.dataTables_wrapper .dataTables_paginate .page-link {
    min-width: 2.25rem;
    height: 2.25rem;
    padding: 0;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    border-color: #e2e8f0;
    box-shadow: none;
}

.dataTables_wrapper .dataTables_paginate .page-link:hover {
    color: var(--primary-color);
    background: #eff6ff;
    border-color: #bfdbfe;
}

.dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
    background: #009688;
    border-color: #009688;
    color: white;
}

.dataTables_wrapper .dataTables_paginate .page-item.disabled .page-link {
    color: #cbd5e1;
    background: #f8fafc;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0.4rem 0.65rem;
}

.table-modern thead {
    background: #f8fafc;
}

.table-modern th {
    border: none;
    padding: 1rem;
    font-weight: 600;
    color: var(--secondary-color);
}

#tabla-pedidos thead th,
#tabla-pedidos thead th a,
#tabla-pedidos thead th span,
#tabla-pedidos thead .dt-column-title {
    color: white !important;
}

#tabla-pedidos thead {
    background: var(--primary-color) !important;
}

#tabla-pedidos thead th {
    background: var(--primary-color) !important;
    border-top: none;
    border-bottom: none;
}

.table-modern td {
    border: none;
    padding: 1rem;
    border-top: 1px solid #f1f5f9;
}

.alert-modern {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
}

.alert-success-modern {
    background: #dcfce7;
    color: var(--success-color);
    border-left: 4px solid var(--success-color);
}

.alert-danger-modern {
    background: #fee2e2;
    color: var(--danger-color);
    border-left: 4px solid var(--danger-color);
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php include_once "footer.php"; ?>
