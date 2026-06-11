<?php
include_once "header.php";
require_once "../models/database.php";

// Procesamiento del formulario de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    // Obtener datos del formulario
    $usuario = $_POST['usuario_usuario'] ?? '';
    $password = $_POST['usuario_password'] ?? '';
    $password_confirm = $_POST['usuario_password_conf'] ?? '';
    $email = $_POST['usuario_email'] ?? '';
    $telefono = $_POST['usuario_telefono'] ?? '';
    $direccion = $_POST['usuario_direccion'] ?? '';
    $nombre = $_POST['usuario_nombre'] ?? '';
    $rol = $_POST['usuario_rol'] ?? '';
    
    // Validación básica
    $errores = [];
    if (empty($usuario)) {
        $errores[] = "El nombre de usuario es obligatorio";
    }
    if (empty($password)) {
        $errores[] = "La contraseña es obligatoria";
    }
    if ($password !== $password_confirm) {
        $errores[] = "Las contraseñas no coinciden";
    }
    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido";
    }
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    if (!in_array($rol, ['1', '2', '3', '4'], true)) {
        $errores[] = "Debe seleccionar un rol valido";
    }
    
    // Si no hay errores, proceder con la inserción
    if (empty($errores)) {
        // Hashear la contraseña para seguridad
        try {
            $db = new Database();
            $conn = $db->connect();

            $stmtExiste = $conn->prepare("SELECT COUNT(*) FROM users WHERE usuario = :usuario");
            $stmtExiste->execute([
                "usuario" => $usuario
            ]);

            if ((int) $stmtExiste->fetchColumn() > 0) {
                $errores[] = "Ya existe un usuario con ese nombre de usuario";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO users (usuario, password, email, telefono, direccion, nombre, rol)
                    VALUES (:usuario, :password, :email, :telefono, :direccion, :nombre, :rol)
                ");
                $stmt->execute([
                    "usuario" => $usuario,
                    "password" => $password,
                    "email" => $email,
                    "telefono" => $telefono,
                    "direccion" => $direccion,
                    "nombre" => $nombre,
                    "rol" => $rol
                ]);

                $mensaje_exito = "El usuario ha sido registrado exitosamente";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al insertar: " . $e->getMessage();
        }
        
        // Preparar la consulta SQL
        // $sql_insert = "INSERT INTO users (usuario, password, email, telefono, direccion, nombre, rol) 
        //                VALUES ('$usuario', '$password_hash', '$email', '$telefono', '$direccion', '$nombre', '$rol')";
        
        // try {
        //     $db = new Database();
        //     $conn = $db->connect();
        //     $stmt = $conn->prepare($sql_insert);
        //     $stmt->execute();
        //     $mensaje_exito = "El usuario ha sido registrado exitosamente";
        // } catch (PDOException $e) {
        //     $errores[] = "Error al insertar: " . $e->getMessage();
        // }
        
        // Simulación (eliminar en producción)
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);

    if ($usuarioId) {
        try {
            $db = new Database();
            $stmt = $db->connect()->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(["id" => $usuarioId]);
            $mensaje_exito = "El usuario ha sido eliminado exitosamente";
        } catch (PDOException $e) {
            $errores[] = "Error al eliminar: " . $e->getMessage();
        }
    } else {
        $errores[] = "No se pudo identificar el usuario a eliminar";
    }
}

class User {
    private $db;

    public function __construct() {
        $this->db = new Database(); // Crear instancia de la base de datos
    }

    public function getUsers() {
        try {
            $query = $this->db->connect()->prepare("SELECT `id`, `usuario`, `password`, `email`, `telefono`, `direccion`, `nombre`, `rol` FROM `users` ");
            $query->execute(); // Ejecutar la consulta
            return $query->fetchAll(PDO::FETCH_ASSOC); // Obtener todos los resultados como array asociativo
        } catch (PDOException $e) {
            die("Error en la consulta: " . $e->getMessage());
        }
    }
}

// Crear instancia de User y obtener los usuarios
$user = new User();
$users = $user->getUsers();
$rolesUsuario = [
    0 => 'Sin rol',
    1 => 'Administrador',
    2 => 'Vendedor',
    3 => 'Supervisor',
    4 => 'Cajero'
];
?>

<!-- Page header -->
<div class="module-header">
    <div class="d-flex align-items-center">
        <i class="fas fa-user-cog fa-2x mr-3"></i>
        <div>
            <h1 class="mb-1 font-weight-bold">Gestión de Usuarios</h1>
            <p class="mb-0 opacity-90">Administra usuarios, roles y accesos del sistema</p>
        </div>
    </div>
</div>
<div style="display: none;">
    <p class="text-justify">
        Nota: Tenga en cuenta que los usuarios tendrán acceso al sistema según su rol asignado. Complete todos los campos obligatorios y asigne los permisos adecuados.
    </p>
</div>

<!-- Mensaje de éxito o error -->
<?php if (isset($mensaje_exito)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>¡Éxito!</strong> <?php echo $mensaje_exito; ?>
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

<!-- Formulario de inserción -->
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-4">
        <button type="button" class="btn btn-modern btn-primary-modern" data-toggle="modal" data-target="#usuarioModal">
            <i class="fas fa-plus-circle mr-2"></i>Agregar usuario
        </button>
    </div>
</div>

<!-- Modal de inserciÃ³n -->
<div class="modal fade" id="usuarioModal" tabindex="-1" role="dialog" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content user-modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-plus text-primary mr-2"></i>
                    <h5 class="modal-title mb-0 font-weight-bold" id="usuarioModalLabel">Nuevo Usuario</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                    <form action="" method="POST" id="form-usuario" class="form-neon" autocomplete="off">
                        <input type="hidden" name="action" value="insert">
                        
                        <fieldset>
                            <legend><i class="fas fa-user"></i> &nbsp; Información básica</legend>
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_nombre" class="bmd-label-floating">Nombre completo <span class="text-danger">*</span></label>
                                            <input type="text" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,35}" class="form-control" name="usuario_nombre" id="usuario_nombre" maxlength="35" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_telefono" class="bmd-label-floating">Teléfono</label>
                                            <input type="text" pattern="[0-9()+]{8,20}" class="form-control" name="usuario_telefono" id="usuario_telefono" maxlength="20">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_direccion" class="bmd-label-floating">Dirección</label>
                                            <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}" class="form-control" name="usuario_direccion" id="usuario_direccion" maxlength="190">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_rol" class="bmd-label-floating">Rol de usuario <span class="text-danger">*</span></label>
                                            <select class="form-control" name="usuario_rol" id="usuario_rol" required>
                                                <option value="" selected disabled>Seleccione un rol</option>
                                                <option value="1">Administrador</option>
                                                <option value="2">Vendedor</option>
                                                <option value="3">Supervisor</option>
                                                <option value="4">Cajero</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        
                        <br>
                        
                        <fieldset>
                            <legend><i class="fas fa-user-lock"></i> &nbsp; Información de la cuenta</legend>
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_usuario" class="bmd-label-floating">Nombre de usuario <span class="text-danger">*</span></label>
                                            <input type="text" pattern="[a-zA-Z0-9]{4,20}" class="form-control" name="usuario_usuario" id="usuario_usuario" maxlength="20" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_email" class="bmd-label-floating">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="usuario_email" id="usuario_email" maxlength="70" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_password" class="bmd-label-floating">Contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="usuario_password" id="usuario_password" maxlength="100" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="usuario_password_conf" class="bmd-label-floating">Confirmar contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="usuario_password_conf" id="usuario_password_conf" maxlength="100" required>
                                            <small id="password-match-feedback" class="password-feedback"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        
                        <p class="text-center border-top pt-3" style="margin-top: 28px;">
                            <button type="reset" class="btn btn-modern btn-outline-modern"><i class="fas fa-undo mr-2"></i>Limpiar</button>
                            <button type="submit" class="btn btn-modern btn-primary-modern ml-2" id="btn-guardar-usuario"><i class="far fa-save mr-2"></i>Guardar usuario</button>
                        </p>
                    </form>
            </div>
        </div>
    </div>
</div>

<br>

<!-- Tabla de usuarios -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 font-weight-bold"><i class="fas fa-clipboard-list fa-fw text-primary"></i> &nbsp; Lista de Usuarios</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern table-sm mb-0" id="tabla-usuarios">
                            <thead>
                                <tr class="text-center roboto-medium">
                                    <th>#</th>
                                    <th>USUARIO</th>
                                    <th>NOMBRE</th>
                                    <th>TELÉFONO</th>
                                    <th>EMAIL</th>
                                    <th>ROL</th>
                                    <th>ACTUALIZAR</th>
                                    <th>ELIMINAR</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            if (!empty($users)) {
                                foreach ($users as $user) { 
                            ?>
                                <tr class="text-center">
                                    <td><?php echo htmlspecialchars($user["id"]); ?></td>
                                    <td><?php echo htmlspecialchars($user["usuario"]); ?></td>
                                    <td><?php echo htmlspecialchars($user["nombre"]); ?></td>
                                    <td><?php echo htmlspecialchars($user["telefono"] ? $user["telefono"] : 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user["email"]); ?></td>
                                    <td><?php echo htmlspecialchars($rolesUsuario[(int) $user["rol"]] ?? 'Rol ' . $user["rol"]); ?></td>
                                    <td>
                                        <a href='user-update.php?id=<?php echo $user["id"]; ?>' class='btn btn-outline-primary btn-sm rounded-action' title="Actualizar">
                                            <i class="fas fa-sync-alt"></i>    
                                        </a>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-btn rounded-action" data-id="<?php echo $user["id"]; ?>" title="Eliminar">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmación de eliminación -->
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<style>
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --light-bg: #f8fafc;
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

.user-modal-content,
.modern-card {
    border: none;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
}

.user-modal-content {
    box-shadow: 0 20px 45px rgb(15 23 42 / 0.18);
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

#tabla-usuarios thead,
#tabla-usuarios thead th {
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

.password-feedback {
    display: block;
    min-height: 1.25rem;
    margin-top: 0.35rem;
    font-size: 0.82rem;
    font-weight: 500;
}

.password-feedback.is-valid {
    color: #059669;
}

.password-feedback.is-invalid {
    color: #dc2626;
}

#usuario_password.is-valid,
#usuario_password_conf.is-valid {
    border-color: #059669;
}

#usuario_password.is-invalid,
#usuario_password_conf.is-invalid {
    border-color: #dc2626;
}
</style>
<script defer src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script defer src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content user-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-outline-modern" data-dismiss="modal">Cancelar</button>
                <form id="delete-form" method="POST" action="">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="usuario_id" id="usuario_id" value="">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para manejar la confirmación de eliminación
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && $.fn.DataTable) {
            $('#tabla-usuarios').DataTable({
                pageLength: 50,
                lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'Todos']],
                order: [[0, 'desc']],
                language: {
                    decimal: '',
                    emptyTable: 'No hay usuarios registrados',
                    info: '_START_ - _END_ de _TOTAL_ usuarios',
                    infoEmpty: '0 usuarios',
                    infoFiltered: '(filtrado de _MAX_ usuarios en total)',
                    lengthMenu: 'Mostrar _MENU_ usuarios',
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                    search: 'Buscar:',
                    zeroRecords: 'No se encontraron usuarios',
                    paginate: {
                        first: '<i class="fas fa-angle-double-left" aria-hidden="true"></i>',
                        last: '<i class="fas fa-angle-double-right" aria-hidden="true"></i>',
                        next: '<i class="fas fa-chevron-right" aria-hidden="true"></i>',
                        previous: '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
                    }
                }
            });
        }

        // Configurar los botones de eliminación para abrir el modal
        const formUsuario = document.getElementById('form-usuario');
        const passwordInput = document.getElementById('usuario_password');
        const passwordConfirmInput = document.getElementById('usuario_password_conf');
        const passwordFeedback = document.getElementById('password-match-feedback');
        const btnGuardarUsuario = document.getElementById('btn-guardar-usuario');

        function validarPasswordsEnVivo() {
            const password = passwordInput.value;
            const confirmacion = passwordConfirmInput.value;
            const hayConfirmacion = confirmacion.length > 0;
            const coinciden = password !== '' && password === confirmacion;

            passwordInput.classList.remove('is-valid', 'is-invalid');
            passwordConfirmInput.classList.remove('is-valid', 'is-invalid');
            passwordFeedback.classList.remove('is-valid', 'is-invalid');

            if (!hayConfirmacion) {
                passwordFeedback.textContent = '';
                btnGuardarUsuario.disabled = false;
                passwordConfirmInput.setCustomValidity('');
                return true;
            }

            if (coinciden) {
                passwordInput.classList.add('is-valid');
                passwordConfirmInput.classList.add('is-valid');
                passwordFeedback.classList.add('is-valid');
                passwordFeedback.textContent = 'Las contraseñas coinciden';
                btnGuardarUsuario.disabled = false;
                passwordConfirmInput.setCustomValidity('');
                return true;
            }

            passwordInput.classList.add('is-invalid');
            passwordConfirmInput.classList.add('is-invalid');
            passwordFeedback.classList.add('is-invalid');
            passwordFeedback.textContent = 'Las contraseñas no coinciden';
            btnGuardarUsuario.disabled = true;
            passwordConfirmInput.setCustomValidity('Las contraseñas no coinciden');
            return false;
        }

        passwordInput.addEventListener('input', validarPasswordsEnVivo);
        passwordConfirmInput.addEventListener('input', validarPasswordsEnVivo);

        formUsuario.addEventListener('submit', function(e) {
            if (!validarPasswordsEnVivo()) {
                e.preventDefault();
                passwordConfirmInput.focus();
            }
        });

        formUsuario.addEventListener('reset', function() {
            setTimeout(validarPasswordsEnVivo, 0);
        });

        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('usuario_id').value = id;
                $('#deleteModal').modal('show');
            });
        });
    });
</script>

</section>
</main>

<?php
include_once "footer.php";
?>
