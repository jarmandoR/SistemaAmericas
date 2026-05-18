<?php
// Mostrar errores en entorno de desarrollo (comentarlos en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

include_once "header.php";
require_once "../models/database.php";
require_once "../models/BarModel.php";

$bar = new Bar();
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$limite = 10; // cantidad por página
$offset = ($pagina - 1) * $limite;

$totalClientes = $bar->contarTotalClientes();
$totalPaginas = ceil($totalClientes / $limite);

$clientes = $bar->obtenerClientes();
$errores = []; // 🔸 Inicializamos el arreglo de errores

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "insert") {
    $razon_social = trim($_POST["cliente_nombre"]);
    $telefono = trim($_POST["cliente_telefono"]);
    $direccion = trim($_POST["cliente_direccion"]);
    $zona = trim($_POST["cliente_zona"]);
    $nombre_bar = trim($_POST["nombre_bar"]);
    $bar_id = trim($_POST["bar_id"]);

    try {
        // Si no se seleccionó un bar de la lista, verificar si existe por nombre
        if (empty($bar_id)) {
            if (!$bar->existeBar($nombre_bar)) {
                $barInsertado = $bar->insertarBar(
                    $nombre_bar,
                    $direccion,
                );
                if ($barInsertado) {
                    $bar_id = $bar->obtenerUltimoIdInsertado();
                } else {
                    $errores[] = "No se pudo insertar el bar.";
                }
            } else {

                $barExistente = $bar->buscarBaresPorNombre($nombre_bar);
                if (count($barExistente) > 0) {
                    $bar_id = $barExistente[0]["id_bar"];
                } else {
                    $errores[] = "No se encontró el bar existente.";
                }
            }
        }

        if (empty($errores)) {
            $clienteInsertado = $bar->insertarCliente($bar_id, $razon_social, $telefono, $direccion, $zona);

            if ($clienteInsertado) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                exit;
            } else {
                $errores[] = "No se pudo registrar el cliente.";
            }
        }
    } catch (Exception $e) {
        $errores[] = "Error interno: " . $e->getMessage();
        // Opcional: guarda el error en un archivo log
        file_put_contents("log.txt", "[" . date("Y-m-d H:i:s") . "] " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}
if ($_POST["action"] === "delete") {
    $cliente_id = $_POST["cliente_id"] ?? null;

    if ($cliente_id) {
        $eliminado = $bar->eliminarCliente($cliente_id);

        if ($eliminado) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit;
        } else {
            $errores[] = "No se pudo eliminar el cliente.";
        }
    } else {
        $errores[] = "ID del cliente no válido.";
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "update") {
    $id_cliente = $_POST["id_cliente"];
    $bar_id     = $_POST["bar_id"];
    $nombre     = $_POST["cliente_nombre"];
    $telefono   = $_POST["cliente_telefono"];
    $direccion  = $_POST["cliente_direccion"];
    $zona       = $_POST["cliente_zona"];

    if ($bar->actualizarCliente($id_cliente, $bar_id, $nombre, $telefono, $direccion, $zona)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit;
    } else {
        $errores[] = "No se pudo actualizar el cliente.";
    }
}
?>

<!-- Page header -->
<div class="module-header">
    <div class="d-flex align-items-center">
        <i class="fas fa-user-tie fa-2x mr-3"></i>
        <div>
            <h1 class="mb-1 font-weight-bold">Gestión de Clientes</h1>
            <p class="mb-0 opacity-90">Administra clientes, bares, zonas y datos comerciales</p>
        </div>
    </div>
</div>
<div style="display: none;">
    <p class="text-justify">
        Nota: Tenga en cuenta que la información registrada será utilizada para todas las operaciones comerciales. Complete todos los campos con información precisa y actualizada.
    </p>
</div>

<!-- Mensaje de éxito o error -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Éxito!</strong> Cliente registrado correctamente.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>¡Error!</strong>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

<?php endif; ?>



<!-- Content here-->
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-4">
        <button type="button" class="btn btn-modern btn-primary-modern" data-toggle="modal" data-target="#clienteModal">
            <i class="fas fa-plus-circle mr-2"></i>Agregar cliente
        </button>
    </div>
</div>

<div class="modal fade" id="clienteModal" tabindex="-1" role="dialog" aria-labelledby="clienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content modern-modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-plus text-primary mr-2"></i>
                    <h5 class="modal-title mb-0 font-weight-bold" id="clienteModalLabel">Nuevo Cliente</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                    <form action="" method="POST" class="form-neon" autocomplete="off">
                        <input type="hidden" name="action" value="insert">
                        <fieldset>
                            <legend><i class="fas fa-user"></i> &nbsp; Información básica</legend>
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group position-relative">
                                            <label for="nombre_bar" class="bmd-label-floating">Bar <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nombre_bar" id="nombre_bar" maxlength="40" required>
                                            <input type="hidden" name="bar_id" id="bar_id" value="">
                                            <!-- Lista de sugerencias -->
                                            <div id="autocomplete-list" class="autocomplete-suggestions" style="display: none;">
                                                <!-- Las sugerencias se cargarán aquí dinámicamente -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="cliente_nombre" class="bmd-label-floating">Cliente</label>
                                            <input type="text" class="form-control" name="cliente_nombre" id="cliente_nombre" maxlength="20" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="cliente_telefono" class="bmd-label-floating">Teléfono</label>
                                            <input type="text" pattern="[0-9()+]{1,20}" class="form-control" name="cliente_telefono" id="cliente_telefono" maxlength="20" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="cliente_direccion" class="bmd-label-floating">Dirección</label>
                                            <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ#- ]{1,150}" class="form-control" name="cliente_direccion" id="cliente_direccion" maxlength="150" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="cliente_zona" class="bmd-label-floating">Zona</label>
                                            <select class="form-control" name="cliente_zona" id="cliente_zona" required>
                                                <option value="" disabled selected>Seleccione una zona</option>
                                                <option value="Chapinero">Chapinero</option>
                                                <option value="Centro">Centro</option>
                                                <option value="Zona T">Zona T</option>
                                                <option value="45">45</option>
                                                <option value="Otra">Otra</option>
                                                <!-- Agrega más opciones según tu necesidad -->
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </fieldset>
                        <p class="text-center border-top pt-3" style="margin-top: 28px;">
                            <button type="reset" class="btn btn-modern btn-outline-modern"><i class="fas fa-undo mr-2"></i>Limpiar</button>
                            <button type="submit" class="btn btn-modern btn-primary-modern ml-2"><i class="far fa-save mr-2"></i>Guardar cliente</button>
                        </p>
                    </form>
            </div>
        </div>
    </div>
</div>
<br>

<!-- Tabla de clientes -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 font-weight-bold"><i class="fas fa-list text-primary"></i> &nbsp; Lista de Clientes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern table-sm mb-0" id="tabla-clientes">
                            <thead>
                                <tr class="text-center roboto-medium">
                                    <th>#</th>
                                    <th>Razón Social</th>
                                    <th>CLIENTE</th>
                                    <th>TELÉFONO</th>
                                    <th>DIRECCIÓN</th>
                                    <th>ZONA</th>
                                    <th>FECHA REGISTRO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $index => $cliente): ?>
                                    <tr class="text-center">
                                        <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                                        <td><?= htmlspecialchars($cliente['nombre_bar']) ?></td>
                                        <td><?= htmlspecialchars($cliente['cli_nombre']) ?></td>
                                        <td><?= htmlspecialchars($cliente['cli_telefono']) ?></td>
                                        <td><?= htmlspecialchars($cliente['cli_direccion']) ?></td>
                                        <td><?= htmlspecialchars($cliente['cli_zona']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($cliente['cli_fecha_registro'])) ?></td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm mr-1 rounded-action edit-btn"
                                                data-toggle="modal"
                                                data-target="#editModal"
                                                data-id="<?= htmlspecialchars($cliente['id_cliente'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-nombre="<?= htmlspecialchars($cliente['cli_nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-telefono="<?= htmlspecialchars($cliente['cli_telefono'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-direccion="<?= htmlspecialchars($cliente['cli_direccion'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-zona="<?= htmlspecialchars($cliente['cli_zona'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-bar="<?= htmlspecialchars($cliente['cli_Bar'], ENT_QUOTES, 'UTF-8') ?>"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-btn rounded-action" data-id="<?= htmlspecialchars($cliente['id_cliente'], ENT_QUOTES, 'UTF-8') ?>" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content modern-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este cliente? Esta acción no se puede deshacer y puede afectar a registros relacionados.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-outline-modern" data-dismiss="modal">Cancelar</button>
                <form id="delete-form" method="POST" action="">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="cliente_id" id="cliente_id" value="">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content modern-modal-content">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_cliente" id="edit_id_cliente">

            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="cliente_nombre" id="edit_nombre" class="form-control">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="cliente_telefono" id="edit_telefono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="cliente_direccion" id="edit_direccion" class="form-control">
                </div>
                <div class="form-group">
                    <label>Zona</label>
                    <input type="text" name="cliente_zona" id="edit_zona" class="form-control">
                </div>
                <div class="form-group">
                    <label>Bar ID</label>
                    <input type="text" name="bar_id" id="edit_bar_id" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-outline-modern" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-modern btn-primary-modern">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" rel="stylesheet">

<!-- Estilos CSS para el autocompletable -->
<style>
    :root {
        --primary-color: #2563eb;
        --secondary-color: #64748b;
        --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
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

    .modern-card,
    .modern-modal-content {
        background: white;
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
    }

    .modern-modal-content {
        box-shadow: 0 20px 45px rgb(15 23 42 / 0.18);
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

    #tabla-clientes thead,
    #tabla-clientes thead th {
        background: var(--primary-color) !important;
        color: white !important;
        border: none;
    }

    .rounded-action {
        border-radius: 6px;
        min-width: 2.25rem;
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

    .autocomplete-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .autocomplete-suggestion {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        color: #333;
    }

    .autocomplete-suggestion:hover,
    .autocomplete-suggestion.selected {
        background-color: #f0f0f0;
    }

    .autocomplete-suggestion:last-child {
        border-bottom: none;
    }

    .form-group.position-relative {
        position: relative;
    }
</style>

<script defer src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script defer src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && $.fn.DataTable) {
            $('#tabla-clientes').DataTable({
                pageLength: 50,
                lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'Todos']],
                order: [[0, 'desc']],
                columnDefs: [
                    { targets: 7, orderable: false, searchable: false }
                ],
                language: {
                    decimal: '',
                    emptyTable: 'No hay clientes registrados',
                    info: '_START_ - _END_ de _TOTAL_ clientes',
                    infoEmpty: '0 clientes',
                    infoFiltered: '(filtrado de _MAX_ clientes en total)',
                    lengthMenu: 'Mostrar _MENU_ clientes',
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                    search: 'Buscar:',
                    zeroRecords: 'No se encontraron clientes',
                    paginate: {
                        first: '<i class="fas fa-angle-double-left" aria-hidden="true"></i>',
                        last: '<i class="fas fa-angle-double-right" aria-hidden="true"></i>',
                        next: '<i class="fas fa-chevron-right" aria-hidden="true"></i>',
                        previous: '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
                    }
                }
            });
        }

        const clienteNombreInput = document.getElementById('nombre_bar');
        const barIdInput = document.getElementById('bar_id');
        const autocompleteList = document.getElementById('autocomplete-list');
        let selectedIndex = -1;

        // Función para buscar bares
        function buscarBares(query) {
            if (query.length < 2) {
                autocompleteList.style.display = 'none';
                return;
            }

            // Realizar petición AJAX
            fetch('../controllers/buscar_bar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'query=' + encodeURIComponent(query)
                })
                .then(response => response.json())
                .then(data => {
                    mostrarSugerencias(data);
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    autocompleteList.style.display = 'none';
                });
        }

        // Función para mostrar sugerencias
        function mostrarSugerencias(data) {
            const autocompleteList = document.getElementById('autocomplete-list');
            autocompleteList.innerHTML = ''; // Limpia anteriores

            if (!Array.isArray(data) || data.length === 0) {
                autocompleteList.style.display = 'none';
                return;
            }

            data.forEach(item => {
                const div = document.createElement('div');
                div.classList.add('autocomplete-suggestion');
                div.textContent = item.label || item.value || 'Sin nombre';

                div.addEventListener('click', () => {
                    document.getElementById('nombre_bar').value = item.label;
                    document.getElementById('bar_id').value = item.id;
                    autocompleteList.innerHTML = '';
                    autocompleteList.style.display = 'none';
                });

                autocompleteList.appendChild(div);
            });

            autocompleteList.style.display = 'block';
        }

        // Función para seleccionar un bar
        function seleccionarBar(element) {
            clienteNombreInput.value = element.dataset.nombre;
            barIdInput.value = element.dataset.id;

            // Auto-llenar otros campos si están disponibles
            if (element.dataset.direccion) {
                document.getElementById('cliente_direccion').value = element.dataset.direccion;
            }
            if (element.dataset.telefono) {
                document.getElementById('cliente_telefono').value = element.dataset.telefono;
            }

            autocompleteList.style.display = 'none';
        }

        // Event listeners
        clienteNombreInput.addEventListener('input', function() {
            const query = this.value.trim();
            buscarBares(query);
        });

        clienteNombreInput.addEventListener('keydown', function(e) {
            const suggestions = autocompleteList.querySelectorAll('.autocomplete-suggestion');

            if (suggestions.length === 0) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % suggestions.length;
                    updateSelection(suggestions);
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = selectedIndex <= 0 ? suggestions.length - 1 : selectedIndex - 1;
                    updateSelection(suggestions);
                    break;

                case 'Enter':
                    e.preventDefault();
                    if (selectedIndex >= 0 && suggestions[selectedIndex]) {
                        seleccionarBar(suggestions[selectedIndex]);
                    }
                    break;

                case 'Escape':
                    autocompleteList.style.display = 'none';
                    selectedIndex = -1;
                    break;
            }
        });

        // Función para actualizar la selección visual
        function updateSelection(suggestions) {
            suggestions.forEach((suggestion, index) => {
                suggestion.classList.toggle('selected', index === selectedIndex);
            });
        }

        // Ocultar sugerencias al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!clienteNombreInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.style.display = 'none';
            }
        });

        // Configurar los botones de eliminación para abrir el modal
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('cliente_id').value = id;
                $('#deleteModal').modal('show');
            });
        });

        // Limpiar campos al reset
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            setTimeout(() => {
                barIdInput.value = '';
                autocompleteList.style.display = 'none';
            }, 10);
        });
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id_cliente').value = this.dataset.id;
            document.getElementById('edit_nombre').value = this.dataset.nombre;
            document.getElementById('edit_telefono').value = this.dataset.telefono;
            document.getElementById('edit_direccion').value = this.dataset.direccion;
            document.getElementById('edit_zona').value = this.dataset.zona;
            document.getElementById('edit_bar_id').value = this.dataset.bar;
        });
    });
</script>

</section>
</main>

<?php
include_once "footer.php";
?>
