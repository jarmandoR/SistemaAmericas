<?php
ob_start();
include_once "header.php"; 
require_once "../models/ProductoModel.php";

// Obtener productos desde la base de datos
$producto = new Producto();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'actualizar_producto') {
    $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
    $codigoProducto = trim($_POST['codigo_productos'] ?? '');
    $descripcionProducto = trim($_POST['descripcion_producto'] ?? '');
    $cantidadPaca = $_POST['cantidad_paca_producto'] ?? 0;
    $precioUnidad = $_POST['precio_unidad_producto'] ?? 0;
    $precioPaca = $_POST['precio_paca_producto'] ?? 0;
    $categoria = $_POST['id_cate_producto'] ?? 1;
    $actiUnidad = $_POST['acti_Unidad'] ?? 0;
    $imagenProducto = trim($_POST['imagen_producto'] ?? '');
    $estadoProducto = $_POST['estado_producto'] ?? '1';

    try {
        if (!$idProducto || $codigoProducto === '' || $descripcionProducto === '') {
            throw new Exception('Completa los campos obligatorios del producto.');
        }

        $actualizado = $producto->actualizarProducto(
            $idProducto,
            $codigoProducto,
            $descripcionProducto,
            $cantidadPaca,
            $precioUnidad,
            $precioPaca,
            $categoria,
            $actiUnidad,
            $imagenProducto,
            $estadoProducto
        );

        if (!$actualizado) {
            throw new Exception('No se pudo actualizar el producto.');
        }

        $success = 'producto_actualizado';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cambiar_estado_producto') {
    $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
    $estadoProducto = $_POST['estado_producto'] ?? null;
    $esAjax = ($_POST['ajax'] ?? '') === '1';

    try {
        if (!$idProducto || !in_array($estadoProducto, ['0', '1'], true)) {
            throw new Exception('No se pudo cambiar el estado del producto.');
        }

        $db = new Database();
        $stmt = $db->connect()->prepare("
            UPDATE productos
            SET estado_producto = :estado_producto
            WHERE id_producto = :id_producto
        ");
        $stmt->execute([
            'estado_producto' => $estadoProducto,
            'id_producto' => $idProducto
        ]);

        if ($esAjax) {
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado',
                'estado' => $estadoProducto
            ]);
            exit;
        }

        $success = 'estado_actualizado';
    } catch (Exception $e) {
        if ($esAjax) {
            if (ob_get_length()) {
                ob_clean();
            }
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }

        $error = $e->getMessage();
    }
}

$productos = $producto->obtenerProductosLista(0);

// Capturar parámetros de resultado
$importados = $_GET['importados'] ?? 0;
$actualizados = $_GET['actualizados'] ?? 0;
$errores = $_GET['errores'] ?? 0;
$success = $success ?? ($_GET['success'] ?? null);
$error = $error ?? ($_GET['error'] ?? null);
$erroresDetalle = isset($_GET['errores_detalle']) ? explode('|', $_GET['errores_detalle']) : [];
$filasVacias = $_GET['filas_vacias'] ?? 0;
$delimitador = $_GET['delimitador'] ?? null;
$encabezado = $_GET['encabezado'] ?? null;
$preciosActualizados = $_GET['precios_actualizados'] ?? 0;
?>

<!-- Estilos para notificaciones toast -->
<link href="../css/subir_excel.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" rel="stylesheet">

<style>
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.modern-card,
.product-modal-content {
    background: white;
    border: none;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
}

.product-modal-content {
    box-shadow: 0 20px 45px rgb(15 23 42 / 0.18);
}

.rounded-action {
    border-radius: 6px;
    min-width: 2.25rem;
}

.estado-producto-form {
    min-width: 120px;
}

.estado-producto-select {
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.3rem 0.75rem;
    height: auto;
}

.estado-producto-select.estado-activo {
    background: #dcfce7;
    color: #059669;
    border-color: #bbf7d0;
}

.estado-producto-select.estado-inactivo {
    background: #f1f5f9;
    color: #64748b;
    border-color: #e2e8f0;
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
    color: white;
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
    color: var(--primary-color);
}

.table-modern {
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.table-modern th {
    border: none;
    padding: 1rem;
    font-weight: 600;
}

.table-modern td {
    border: none;
    padding: 1rem;
    border-top: 1px solid #f1f5f9;
    vertical-align: middle;
}

#tabla-productos thead,
#tabla-productos thead th {
    background: var(--primary-color) !important;
    color: white !important;
    border: none;
}

.dataTables_wrapper {
    padding: 1rem;
}

.dataTables_wrapper .dataTables_info {
    color: #64748b;
    font-size: 0.875rem;
    padding-top: 0.65rem;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0.4rem 0.65rem;
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

.module-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    margin: 1rem 1.5rem 2rem;
}

.module-header h1 {
    font-size: 2.25rem;
    line-height: 1.1;
}

.module-header p {
    font-size: 1rem;
}
</style>


<!-- Container para las notificaciones toast -->
<div class="toast-container" id="toastContainer"></div>

<!-- Page header -->
<div class="module-header">
    <div class="d-flex align-items-center">
        <i class="fas fa-box-open fa-2x mr-3"></i>
        <div>
            <h1 class="mb-1 font-weight-bold">Gestión de Productos</h1>
            <p class="mb-0 opacity-90">Carga productos en lote, edita precios y administra el catálogo</p>
        </div>
    </div>
</div>
<div style="display: none;">
    <p class="text-justify">
        A continuación se presenta la lista de productos disponibles. Puede cargar productos en lote desde un archivo CSV.
    </p>
</div>

<!-- Estadísticas rápidas -->
<?php if ($success && $success !== 'producto_actualizado'): ?>
<div class="stats-card">
    <div class="row">
        <div class="col-md-4">
            <div class="stat-item">
                <span class="stat-number"><?php echo $importados; ?></span>
                <span class="stat-label">Nuevos productos</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-item">
                <span class="stat-number"><?php echo $actualizados; ?></span>
                <span class="stat-label">Productos actualizados</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($productos); ?></span>
                <span class="stat-label">Total productos</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Formulario de carga de Excel -->
<div class="container-fluid">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-gradient text-white text-center rounded-top-4" style="background:  #009688 ;">
                        <h4 class="mb-0">💰 Actualización Masiva de Precios</h4>
                        <small>Importa productos y actualiza precios automáticamente</small>
                    </div>

                    <div class="card-body p-4">
                        <form id="uploadForm" action="../controllers/ProductoController.php" method="POST" enctype="multipart/form-data">
                            <div class="upload-zone" onclick="document.getElementById('archivo_excel').click()">
                                <div class="upload-icon">💰</div>
                                <h5>Arrastra tu archivo CSV de precios aquí</h5>
                                <p class="text-muted">El sistema actualizará automáticamente los productos existentes</p>
                                <input type="file" name="archivo_excel" id="archivo_excel" class="file-input" accept=".csv" required>
                                <div class="progress-bar">
                                    <div class="progress-fill"></div>
                                </div>
                            </div>

                            <div class="mt-3 text-center">
                                <button type="submit" name="importar" class="btn btn-success btn-lg" id="submitBtn">
                                    <i class="fas fa-upload"></i> Procesar Archivo
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

<!-- Lista de productos -->
<div class="container-fluid mt-4">
    <div class="modern-card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0 font-weight-bold"><i class="fas fa-list text-primary"></i> &nbsp; Lista de Productos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-sm mb-0" id="tabla-productos">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>ID</th>
                            <th>CODIGO</th>
                            <th>PRODUCTO</th>
                            <th>EMBALAGE</th>
                            <th>PRECIO UNIDAD</th>
                            <th>PRECIO PACA</th>
                            <th>CATEGORIA</th>
                            <th>U o P</th>
                            <th>IMAGEN</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if (!empty($productos)): ?>
                            <?php $contador = 1; foreach ($productos as $prod): ?>
                            <tr class="text-center">
                                <td><?php echo htmlspecialchars($prod['id_producto']); ?></td>
                                <td><?php echo htmlspecialchars($prod['codigo_productos']); ?></td>
                                <td><?php echo htmlspecialchars($prod['descripcion_producto']); ?></td>
                                <td><?php echo htmlspecialchars($prod['cantidad_paca_producto']); ?></td>
                                <td>$<?php echo number_format($prod['precio_unidad_producto'],2); ?></td>
                                <td>$<?php echo number_format($prod['precio_paca_producto'], 2); ?></td>
                                <td><?php echo htmlspecialchars($prod['id_cate_producto']); ?></td>
                                <td><?php echo htmlspecialchars($prod['acti_Unidad']); ?></td>
                                <td>
                                    <?php if (!empty($prod['imagen_producto'])): ?>
                                        <a href="<?php echo htmlspecialchars($prod['imagen_producto']); ?>" target="_blank" rel="noopener noreferrer">Ver</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="" method="POST" class="estado-producto-form mb-0">
                                        <input type="hidden" name="action" value="cambiar_estado_producto">
                                        <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($prod['id_producto']); ?>">
                                        <select name="estado_producto"
                                            class="form-control form-control-sm estado-producto-select <?php echo (string) $prod['estado_producto'] === '1' ? 'estado-activo' : 'estado-inactivo'; ?>"
                                            data-estado-anterior="<?php echo htmlspecialchars($prod['estado_producto']); ?>">
                                            <option value="1" <?php echo (string) $prod['estado_producto'] === '1' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="0" <?php echo (string) $prod['estado_producto'] === '0' ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-outline-primary btn-sm rounded-action editar-producto-btn"
                                        title="Editar producto"
                                        data-producto="<?php echo htmlspecialchars(json_encode($prod), ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productoModal" tabindex="-1" role="dialog" aria-labelledby="productoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content product-modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-edit text-primary mr-2"></i>
                    <h5 class="modal-title mb-0 font-weight-bold" id="productoModalLabel">Editar Producto</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form action="" method="POST" id="form-editar-producto" autocomplete="off">
                    <input type="hidden" name="action" value="actualizar_producto">
                    <input type="hidden" name="id_producto" id="edit_id_producto">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_codigo_productos">Codigo <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="codigo_productos" id="edit_codigo_productos" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="edit_descripcion_producto">Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="descripcion_producto" id="edit_descripcion_producto" maxlength="250" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_cantidad_paca_producto">Embalaje</label>
                                <input type="number" class="form-control" name="cantidad_paca_producto" id="edit_cantidad_paca_producto" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_precio_unidad_producto">Precio unidad</label>
                                <input type="number" class="form-control" name="precio_unidad_producto" id="edit_precio_unidad_producto" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_precio_paca_producto">Precio paca</label>
                                <input type="number" class="form-control" name="precio_paca_producto" id="edit_precio_paca_producto" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_id_cate_producto">Categoria</label>
                                <input type="number" class="form-control" name="id_cate_producto" id="edit_id_cate_producto" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_acti_Unidad">Venta por unidad</label>
                                <select class="form-control" name="acti_Unidad" id="edit_acti_Unidad">
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_estado_producto">Estado</label>
                                <select class="form-control" name="estado_producto" id="edit_estado_producto">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit_imagen_producto">Imagen</label>
                                <input type="text" class="form-control" name="imagen_producto" id="edit_imagen_producto" maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="text-center border-top pt-3 mt-3">
                        <button type="button" class="btn btn-modern btn-outline-modern" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-modern btn-primary-modern ml-2">
                            <i class="fas fa-save mr-2"></i>Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once "footer.php"; ?>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable) {
        $('#tabla-productos').DataTable({
            pageLength: 50,
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'Todos']],
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 10, orderable: false, searchable: false }
            ],
            language: {
                decimal: '',
                emptyTable: 'No hay productos registrados',
                info: '_START_ - _END_ de _TOTAL_ productos',
                infoEmpty: '0 productos',
                infoFiltered: '(filtrado de _MAX_ productos en total)',
                lengthMenu: 'Mostrar _MENU_ productos',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: 'Buscar:',
                zeroRecords: 'No se encontraron productos',
                paginate: {
                    first: '<i class="fas fa-angle-double-left" aria-hidden="true"></i>',
                    last: '<i class="fas fa-angle-double-right" aria-hidden="true"></i>',
                    next: '<i class="fas fa-chevron-right" aria-hidden="true"></i>',
                    previous: '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
                }
            }
        });
    }

    document.querySelectorAll('.editar-producto-btn').forEach(button => {
        button.addEventListener('click', function() {
            const producto = JSON.parse(this.dataset.producto);

            document.getElementById('edit_id_producto').value = producto.id_producto || '';
            document.getElementById('edit_codigo_productos').value = producto.codigo_productos || '';
            document.getElementById('edit_descripcion_producto').value = producto.descripcion_producto || '';
            document.getElementById('edit_cantidad_paca_producto').value = producto.cantidad_paca_producto || 0;
            document.getElementById('edit_precio_unidad_producto').value = producto.precio_unidad_producto || 0;
            document.getElementById('edit_precio_paca_producto').value = producto.precio_paca_producto || 0;
            document.getElementById('edit_id_cate_producto').value = producto.id_cate_producto || 1;
            document.getElementById('edit_acti_Unidad').value = String(producto.acti_Unidad ?? 0);
            document.getElementById('edit_estado_producto').value = String(producto.estado_producto ?? '1');
            document.getElementById('edit_imagen_producto').value = producto.imagen_producto || '';

            $('#productoModal').modal('show');
        });
    });

    document.querySelectorAll('.estado-producto-select').forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            const formData = new FormData(form);
            const estadoAnterior = this.dataset.estadoAnterior;

            formData.append('ajax', '1');
            this.disabled = true;

            fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json().then(data => {
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo actualizar el estado');
                }
                return data;
            }))
            .then(data => {
                const estado = String(data.estado);
                this.dataset.estadoAnterior = estado;
                this.classList.toggle('estado-activo', estado === '1');
                this.classList.toggle('estado-inactivo', estado !== '1');
                showToast('success', 'Estado actualizado', 'El estado del producto se guardo correctamente.');
            })
            .catch(error => {
                this.value = estadoAnterior;
                this.classList.toggle('estado-activo', estadoAnterior === '1');
                this.classList.toggle('estado-inactivo', estadoAnterior !== '1');
                showToast('error', 'Error', error.message);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
});

// Sistema de notificaciones toast
function showToast(type, title, message, details = []) {
    const toastContainer = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️'
    };
    
    let detailsHtml = '';
    if (details.length > 0) {
        detailsHtml = '<div class="toast-details">' + details.map(d => '• ' + d).join('<br>') + '</div>';
    }
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
            ${detailsHtml}
        </div>
        <button class="toast-close" onclick="closeToast('${toastId}')">&times;</button>
        <div class="toast-progress"></div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto cerrar después de 5 segundos
    setTimeout(() => closeToast(toastId), 5000);
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }
}

// Mostrar notificaciones según los parámetros URL
<?php if ($success): ?>
    <?php if ($success === 'producto_actualizado'): ?>
        showToast('success', 'Producto actualizado', 'Los cambios se guardaron correctamente.');
    <?php endif; ?>
    <?php if ($success === 'estado_actualizado'): ?>
        showToast('success', 'Estado actualizado', 'El estado del producto se guardo correctamente.');
    <?php endif; ?>
    <?php if ($importados > 0 || $actualizados > 0): ?>
        let message = '';
        if (<?php echo $importados; ?> > 0) message += '<?php echo $importados; ?> productos importados. ';
        if (<?php echo $actualizados; ?> > 0) message += '<?php echo $actualizados; ?> productos actualizados.';
        
        <?php if ($errores > 0): ?>
            showToast('warning', 'Proceso completado', message + ' <?php echo $errores; ?> errores encontrados.', <?php echo json_encode($erroresDetalle); ?>);
        <?php else: ?>
            showToast('success', '¡Éxito!', message);
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php if ($error): ?>
    showToast('error', 'Error', <?php echo json_encode($error); ?>);
<?php endif; ?>

// Drag and drop functionality
const uploadZone = document.querySelector('.upload-zone');
const fileInput = document.getElementById('archivo_excel');
const uploadForm = document.getElementById('uploadForm');
const submitBtn = document.getElementById('submitBtn');
const progressBar = document.querySelector('.progress-bar');
const progressFill = document.querySelector('.progress-fill');

// Drag and drop events
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect();
    }
});

// File selection handler
fileInput.addEventListener('change', handleFileSelect);

function handleFileSelect() {
    const file = fileInput.files[0];
    if (file) {
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        
        // Verificar extensión
        if (!fileName.toLowerCase().endsWith('.csv')) {
            showToast('error', 'Archivo inválido', 'Solo se permiten archivos CSV');
            fileInput.value = '';
            return;
        }
        
        // Verificar tamaño
        if (file.size > 5 * 1024 * 1024) {
            showToast('error', 'Archivo muy grande', 'El archivo no debe superar los 5MB');
            fileInput.value = '';
            return;
        }
        
        // Actualizar UI
        uploadZone.querySelector('h5').textContent = fileName;
        uploadZone.querySelector('p').textContent = `${fileSize} MB - Listo para procesar`;
        uploadZone.querySelector('.upload-icon').textContent = '📄✅';
        
        showToast('success', 'Archivo seleccionado', `${fileName} (${fileSize} MB)`);
    }
}

// Form submission with progress
uploadForm.addEventListener('submit', (e) => {
    if (!fileInput.files[0]) {
        e.preventDefault();
        showToast('error', 'Sin archivo', 'Selecciona un archivo CSV para continuar');
        return;
    }
    
    // Mostrar progreso
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    progressBar.style.display = 'block';
    
    // Simular progreso
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress > 90) progress = 90;
        progressFill.style.width = progress + '%';
    }, 100);
    
    // El formulario se enviará normalmente
    setTimeout(() => {
        clearInterval(interval);
        progressFill.style.width = '100%';
    }, 1000);
});

// Limpiar parámetros URL después de mostrar notificaciones
if (window.location.search) {
    const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.replaceState({path: url}, '', url);
}
</script>
