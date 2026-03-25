<?php 
include_once "header.php"; 
require_once "../models/ProductoModel.php";

// Obtener productos desde la base de datos
$producto = new Producto();
$productos = $producto->obtenerProductosLista(0);

// Capturar parámetros de resultado
$importados = $_GET['importados'] ?? 0;
$actualizados = $_GET['actualizados'] ?? 0;
$errores = $_GET['errores'] ?? 0;
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
$erroresDetalle = isset($_GET['errores_detalle']) ? explode('|', $_GET['errores_detalle']) : [];
$filasVacias = $_GET['filas_vacias'] ?? 0;
$delimitador = $_GET['delimitador'] ?? null;
$encabezado = $_GET['encabezado'] ?? null;
$preciosActualizados = $_GET['precios_actualizados'] ?? 0;
?>

<!-- Estilos para notificaciones toast -->
<link href="../css/subir_excel.css" rel="stylesheet" type="text/css" />


<!-- Container para las notificaciones toast -->
<div class="toast-container" id="toastContainer"></div>

<!-- Page header -->
<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-box-open fa-fw"></i> &nbsp; PRODUCTOS
    </h3>
    <p class="text-justify">
        A continuación se presenta la lista de productos disponibles. Puede cargar productos en lote desde un archivo CSV.
    </p>
</div>

<!-- Estadísticas rápidas -->
<?php if ($success): ?>
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
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-list"></i> &nbsp; Lista de Productos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-sm">
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
                        </tr>
                    </thead>
                    <tbody>

                        <?php if (!empty($productos)): ?>
                            <?php $contador = 1; foreach ($productos as $prod): ?>
                            <tr class="text-center">
                                <td><?php echo $prod['id_producto']; ?></td>
                                <td><?php echo $prod['codigo_productos']; ?></td>
                                <td><?php echo $prod['descripcion_producto']; ?></td>
                                <td><?php echo $prod['cantidad_paca_producto']; ?></td>
                                <td>$<?php echo number_format($prod['precio_unidad_producto'],2); ?></td>
                                <td>$<?php echo number_format($prod['precio_paca_producto'], 2); ?></td>
                                <td><?php echo $prod['id_cate_producto']; ?></td>
                                <td><?php echo $prod['acti_Unidad']; ?></td>
                                <td><?php echo $prod['imagen_producto']; ?></td>
                                <td><?php echo $prod['estado_producto']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center">No hay productos registrados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once "footer.php"; ?>

<script>
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