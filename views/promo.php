

<?php
include_once "header.php";
require_once "../models/database.php";

// Procesamiento del formulario (mantener código original)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    
    $errores = [];
    if (empty($titulo)) {
        $errores[] = "El título de la promoción es obligatorio";
    }
    if (empty($descripcion)) {
        $errores[] = "La descripción es obligatoria";
    }
    
    if (empty($errores)) {
        $mensaje_exito = "La promoción ha sido creada exitosamente";
    }
}
?>

<div class="container-fluid px-4 py-3">
    <!-- Header Moderno -->
    <div class="page-header">
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-gift fa-2x me-3"></i>
            <div>
                <h1 class="mb-0 fw-bold">Gestión de Promociones</h1>
                <p class="mb-0 opacity-90">Administra y envía promociones a tus clientes</p>
            </div>
        </div>
    </div>

    <!-- Alertas Modernas -->
    <?php if (isset($mensaje_exito)): ?>
    <div class="alert alert-modern alert-success-modern d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <div>
            <strong>¡Perfecto!</strong> <?php echo $mensaje_exito; ?>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
     <div id="alert-container"></div>
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

    <!-- Formulario Moderno -->
    <div class="d-flex justify-content-end mb-4">
        <button type="button" class="btn btn-modern btn-primary-modern" id="btn-agregar-promo" data-bs-toggle="modal" data-bs-target="#promoModal">
            <i class="fas fa-plus-circle me-2"></i>Agregar promo
        </button>
    </div>

    <div class="modal fade" id="promoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content promo-modal-content">
                <div class="modal-header">
            <div class="d-flex align-items-center mb-0">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                <h5 class="mb-0 fw-semibold" id="form-title">Nueva Promoción</h5>
            </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
            
            <form method="POST" action="" id="form-promocion">
                <input type="hidden" name="action" id="action" value="insert">
                <input type="hidden" name="id" id="id">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="titulo" class="form-label fw-medium">
                            Título de la Promoción <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               placeholder="Ej: Descuento Verano 2025" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="codigo" class="form-label fw-medium">
                            Código del Producto <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="codigo" name="codigo" min="1"
                               placeholder="Ej: 1001" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="estado" class="form-label fw-medium">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="1" selected>Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="prioridad" class="form-label fw-medium">Prioridad</label>
                        <input type="number" class="form-control" id="prioridad" name="prioridad" min="0" value="0">
                    </div>

                    <div class="col-md-4">
                        <label for="precio_unidad_producto" class="form-label fw-medium">Precio Unidad</label>
                        <input type="number" class="form-control" id="precio_unidad_producto" name="precio_unidad_producto" min="0" value="0">
                    </div>

                    <div class="col-md-4">
                        <label for="precio_paca_producto" class="form-label fw-medium">Precio Paca</label>
                        <input type="number" class="form-control" id="precio_paca_producto" name="precio_paca_producto" min="0" value="0">
                    </div>

                    <div class="col-md-4">
                        <label for="acti_Unidad" class="form-label fw-medium">Venta por Unidad</label>
                        <select class="form-select" id="acti_Unidad" name="acti_Unidad">
                            <option value="1" selected>Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="imagen" class="form-label fw-medium">Imagen</label>
                        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
                    </div>
                    
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-medium">
                            Descripción <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control mb-3" id="descripcion" name="descripcion" 
                                  rows="4" placeholder="Descripción detallada de la promoción" required></textarea>
                        
                        <button type="button" id="abrir-emojis" class="btn btn-outline-modern btn-sm">
                            <i class="fas fa-smile me-1"></i> Agregar Emoji
                        </button>
                        
                        <div id="emoji-box" class="emoji-picker mt-2" style="display: none; position: absolute; z-index: 1000;">
                            <div class="d-flex flex-wrap">
                                <span class="emoji">🍺</span> <span class="emoji">🍻</span> <span class="emoji">🥂</span>
                                <span class="emoji">🍷</span> <span class="emoji">🥃</span> <span class="emoji">🍸</span>
                                <span class="emoji">🍹</span> <span class="emoji">🍾</span> <span class="emoji">🧉</span>
                                <span class="emoji">🍶</span> <span class="emoji">🥴</span> <span class="emoji">😵</span>
                                <span class="emoji">🎉</span> <span class="emoji">🎊</span> <span class="emoji">🎈</span>
                                <span class="emoji">🥳</span> <span class="emoji">🔥</span> <span class="emoji">🎵</span>
                                <span class="emoji">🎶</span> <span class="emoji">💃</span> <span class="emoji">🕺</span>
                                <span class="emoji">😎</span> <span class="emoji">😍</span> <span class="emoji">😂</span>
                                <span class="emoji">🤣</span> <span class="emoji">😁</span> <span class="emoji">🥰</span>
                                <span class="emoji">🤩</span> <span class="emoji">💯</span> <span class="emoji">👍</span>
                                <span class="emoji">🍕</span> <span class="emoji">🌮</span> <span class="emoji">🍔</span>
                                <span class="emoji">🍟</span> <span class="emoji">🌭</span> <span class="emoji">🍗</span>
                                <span class="emoji">🚬</span> <span class="emoji">💵</span> <span class="emoji">🤑</span>
                                <span class="emoji">💥</span> <span class="emoji">💫</span> <span class="emoji">🌟</span>
                                <span class="emoji">🎯</span> <span class="emoji">📸</span> <span class="emoji">🕹️</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-3 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-modern btn-primary-modern">
                        <i class="fas fa-save me-2"></i>Guardar Promoción
                    </button>
                    <button type="reset" class="btn btn-modern btn-outline-modern">
                        <i class="fas fa-undo me-2"></i>Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </div>

    <!-- Tabla Moderna -->
    <div class="modern-card">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list text-primary me-2"></i>
                    <h5 class="mb-0 fw-semibold">Lista de Promociones</h5>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-modern mb-0" id="tabla-promos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Promoción</th>
                            <th>Código</th>
                            <th>Precio Paca</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Descripción</th>
                            <th>Imagen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Contenido dinámico -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Moderno -->
<div class="modal fade" id="estadoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p id="modal-mensaje" class="text-muted">¿Está seguro de que desea realizar esta acción?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-modern btn-outline-modern" data-bs-dismiss="modal">Cancelar</button>
                <form id="estado-form" method="POST" action="" class="d-inline">
                    <input type="hidden" name="action" value="cambiar_estado">
                    <input type="hidden" name="promocion_id" id="promocion_id">
                    <input type="hidden" name="nuevo_estado" id="nuevo_estado">
                    <button type="submit" class="btn btn-modern btn-primary-modern">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mantener todo el JavaScript original pero con mejoras visuales
let dataTablesCargando = false;

document.addEventListener('DOMContentLoaded', function() {
    // Emoji picker mejorado
    document.getElementById('abrir-emojis').addEventListener('click', function (e) {
        const box = document.getElementById('emoji-box');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    });

    // Cerrar emoji picker al hacer click fuera
    document.addEventListener('click', function(e) {
        const emojiBox = document.getElementById('emoji-box');
        const emojiBtn = document.getElementById('abrir-emojis');
        if (!emojiBox.contains(e.target) && !emojiBtn.contains(e.target)) {
            emojiBox.style.display = 'none';
        }
    });

    // Agregar emoji al textarea
    document.querySelectorAll('.emoji').forEach(emoji => {
        emoji.addEventListener('click', function() {
            const textarea = document.getElementById('descripcion');
            textarea.value += this.textContent;
            textarea.focus();
        });
    });

    const formPromocion = document.getElementById('form-promocion');
    const btnAgregarPromo = document.getElementById('btn-agregar-promo');

    btnAgregarPromo.addEventListener('click', function() {
        formPromocion.reset();
        resetPromoForm();
    });

    formPromocion.addEventListener('reset', function() {
        setTimeout(resetPromoForm, 0);
    });

    // Guardar o actualizar promocion
    document.getElementById('form-promocion').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        // Mostrar loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
        submitBtn.disabled = true;

        fetch('../controllers/promoController.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Mostrar alerta de éxito moderna
                showAlert('success', '✅ Promoción guardada con éxito');
                form.reset();
                resetPromoForm();
                cargarPromociones();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('promoModal')).hide();
            } else {
                showAlert('error', '⚠️ Error al guardar: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            showAlert('error', '❌ Error de red o servidor');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    cargarPromociones();
});

function cargarPromociones() {
    fetch('../controllers/promoController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=mostrar'
    })
    .then(response => response.json())
    .then(data => {
        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#tabla-promos')) {
            $('#tabla-promos').DataTable().destroy();
        }

        const tbody = document.querySelector("#tabla-promos tbody");
        tbody.innerHTML = '';
        const promociones = data.promociones || data;

        promociones.forEach(promo => {
            const statusClass = String(promo.estado) === '1' ? 'status-active' : 'status-inactive';
            const estadoTexto = String(promo.estado) === '1' ? 'Activa' : 'Inactiva';
            const estadoChecked = String(promo.estado) === '1' ? 'checked' : '';
            const imagen = promo.imagen ? `../assets/img/licores/promos/${promo.imagen}` : '../assets/img/licores/placeholder.jpg';
            
            const promoJson = encodeURIComponent(JSON.stringify(promo));
            const fila = `
                <tr>
                    <td><span class="fw-medium">${promo.id_promocion }</span></td>
                    <td><span class="fw-medium">${promo.titulo}</span></td>
                    <td>${promo.codigo || '-'}</td>
                    <td>$${Number(promo.precio_paca_producto || 0).toLocaleString('es-CO')}</td>
                    <td>${promo.creado_en || '-'}</td>
                    <td>
                        <div class="form-check form-switch promo-status-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="estadoPromo${promo.id_promocion}"
                                   ${estadoChecked}
                                   onchange="cambiarEstadoPromo(this, ${promo.id_promocion})">
                            <label class="form-check-label status-badge ${statusClass}" for="estadoPromo${promo.id_promocion}">
                                ${estadoTexto}
                            </label>
                        </div>
                    </td>
                    <td>
                      <span class="text-muted" style="max-width: 200px; display: inline-block; word-wrap: break-word; white-space: normal;">
                        ${promo.descripcion}
                      </span>
                    </td>
                    <td>
                        <div style="width: 50px; height: 50px; background: #f8fafc; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img src="${imagen}" style="width: 100%; height: 100%; object-fit: cover;" alt="Promocion">
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="editarPromo('${promoJson}')" title="Editar" style="border-radius: 6px;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="enviarPromoDesdeJson('${promoJson}')" title="Enviar" style="border-radius: 6px;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            tbody.innerHTML += fila;
        });

        inicializarTablaPromos();
    })
    .catch(error => console.error("Error:", error));
}

function inicializarTablaPromos() {
    if (!window.jQuery) {
        return;
    }

    if (!$.fn.DataTable) {
        cargarDataTables(function() {
            inicializarTablaPromos();
        });
        return;
    }

    $('#tabla-promos').DataTable({
        pageLength: 50,
        lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'Todos']],
        order: [[0, 'desc']],
        language: {
            decimal: '',
            emptyTable: 'No hay promociones registradas',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ promociones',
            infoEmpty: 'Mostrando 0 a 0 de 0 promociones',
            infoFiltered: '(filtrado de _MAX_ promociones en total)',
            lengthMenu: 'Mostrar _MENU_ promociones',
            loadingRecords: 'Cargando...',
            processing: 'Procesando...',
            search: 'Buscar:',
            zeroRecords: 'No se encontraron promociones',
            paginate: {
                first: 'Primero',
                last: 'Ultimo',
                next: 'Siguiente',
                previous: 'Anterior'
            }
        }
    });
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
    };
    document.body.appendChild(script);
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success-modern' : 'alert-danger-modern';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    const alertHtml = `
        <div class="alert alert-modern ${alertClass} d-flex align-items-center mb-4" role="alert">
            <i class="fas ${icon} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>`;
    
    const container = document.querySelector('.container-fluid');
    const firstCard = container.querySelector('.modern-card');
    firstCard.insertAdjacentHTML('beforebegin', alertHtml);
    
    // Auto-hide después de 5 segundos
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function editarPromo(promoJson) {
    const promo = JSON.parse(decodeURIComponent(promoJson));

    document.getElementById('form-title').textContent = 'Editar Promoción';
    document.getElementById('action').value = 'update';
    document.getElementById('id').value = promo.id_promocion || '';
    document.getElementById('titulo').value = promo.titulo || '';
    document.getElementById('codigo').value = promo.codigo || '';
    document.getElementById('estado').value = String(promo.estado ?? '1');
    document.getElementById('prioridad').value = promo.prioridad || 0;
    document.getElementById('precio_unidad_producto').value = promo.precio_unidad_producto || 0;
    document.getElementById('precio_paca_producto').value = promo.precio_paca_producto || 0;
    document.getElementById('acti_Unidad').value = String(promo.acti_Unidad ?? '1');
    document.getElementById('descripcion').value = promo.descripcion || '';
    document.getElementById('imagen').required = false;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('promoModal')).show();
}

function resetPromoForm() {
    document.getElementById('form-title').textContent = 'Nueva Promoción';
    document.getElementById('action').value = 'insert';
    document.getElementById('id').value = '';
    document.getElementById('imagen').required = true;
}

function cambiarEstadoPromo(input, idPromo) {
    const nuevoEstado = input.checked ? 1 : 0;
    const label = input.closest('.promo-status-switch').querySelector('.form-check-label');

    input.disabled = true;
    actualizarTextoEstado(label, nuevoEstado);

    const formData = new FormData();
    formData.append('action', 'cambiarEstado');
    formData.append('id', idPromo);
    formData.append('estado', nuevoEstado);

    fetch('../controllers/promoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            input.checked = !input.checked;
            actualizarTextoEstado(label, input.checked ? 1 : 0);
            showAlert('error', data.message || 'No se pudo actualizar el estado.');
            return;
        }

        showAlert('success', data.message || 'Estado actualizado correctamente.');
    })
    .catch(error => {
        console.error('Error al cambiar estado:', error);
        input.checked = !input.checked;
        actualizarTextoEstado(label, input.checked ? 1 : 0);
        showAlert('error', 'Error de red al actualizar el estado.');
    })
    .finally(() => {
        input.disabled = false;
    });
}

function actualizarTextoEstado(label, estado) {
    label.textContent = estado === 1 ? 'Activa' : 'Inactiva';
    label.classList.toggle('status-active', estado === 1);
    label.classList.toggle('status-inactive', estado !== 1);
}

function enviarPromoDesdeJson(promoJson) {
    const promo = JSON.parse(decodeURIComponent(promoJson));
    enviarPromo(promo.id_promocion, promo.descripcion, promo.imagen);
}

function enviarPromo(id, descripcion, imagen) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('texto', descripcion);
    formData.append('imagen1', imagen);
    formData.append('action', 'enviarPromo');

    fetch('../controllers/promoController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            showAlert('error', 'Error: ' + result.error);
            alert('❌ Hubo un error al enviar la promoción.');
        } else {
            showAlert('success', 'Promoción enviada correctamente.');
            alert('✅ Promoción enviada con éxito.');
            console.log(result);
        }
    })
    .catch(error => {
        console.error('Error al enviar la promoción:', error);
        showAlert('error', 'Error de red al enviar la promoción.');
    });
}
function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');

    // Elimina alertas anteriores si existen
    alertContainer.innerHTML = '';

    // Define la clase según el tipo
    const alertClass = type === 'success'
        ? 'alert-success-modern'
        : 'alert-danger-modern';

    const icon = type === 'success'
        ? '<i class="bi bi-check-circle-fill me-2"></i>'
        : '<i class="bi bi-exclamation-triangle-fill me-2"></i>';

    const alert = document.createElement('div');
    alert.className = `alert alert-modern ${alertClass} d-flex align-items-center mb-4`;
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `${icon} ${message}`;

    alertContainer.appendChild(alert);

    // Ocultar automáticamente después de 4 segundos
    setTimeout(() => {
        alert.remove();
    }, 4000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.2/dist/index.min.js"></script>
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

.promo-modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 20px 45px rgb(15 23 42 / 0.18);
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

.emoji-picker {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    padding: 1rem;
    max-width: 320px;
    max-height: 240px;
    overflow-y: auto;
}

.emoji-picker .emoji {
    display: inline-block;
    padding: 0.5rem;
    margin: 0.125rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.emoji-picker .emoji:hover {
    background-color: #f1f5f9;
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

.table-modern td {
    border: none;
    padding: 1rem;
    border-top: 1px solid #f1f5f9;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.promo-status-switch {
    min-width: 120px;
}

.promo-status-switch .form-check-input {
    cursor: pointer;
}

.promo-status-switch .form-check-label {
    margin-left: 0.35rem;
    cursor: pointer;
}

.status-active {
    background: #dcfce7;
    color: var(--success-color);
}

.status-inactive {
    background: #f1f5f9;
    color: var(--secondary-color);
}

.status-scheduled {
    background: #fef3c7;
    color: var(--warning-color);
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
</style>

<?php include_once "footer.php"; ?>
