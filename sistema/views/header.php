<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.html?error=Debes%20iniciar%20sesi%C3%B3n");
    exit();
}
date_default_timezone_set('America/Bogota');

$currentPage = basename($_SERVER['PHP_SELF']);
$menuItems = [
    ['href' => 'home.php', 'icon' => 'fab fa-dashcube', 'label' => 'Home'],
    ['href' => 'clientes.php', 'icon' => 'fas fa-address-book', 'label' => 'Clientes'],
    ['href' => 'promo.php', 'icon' => 'fas fa-bullhorn', 'label' => 'Promociones'],
    // ['href' => 'cartera.php', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Pedidos'],
    ['href' => 'user-list.php', 'icon' => 'fas fa-user', 'label' => 'Usuarios'],
    ['href' => 'solicitudes.php', 'icon' => 'fas fa-shopping-cart', 'label' => 'Solicitudes'],
    ['href' => 'categorias.php', 'icon' => 'fas fa-th-large', 'label' => 'Catalogo'],
    ['href' => 'Subir_excel_producto.php', 'icon' => 'fas fa-file-upload', 'label' => 'Cargar Productos'],
];
$currentLabel = 'Panel';
foreach ($menuItems as $item) {
    if ($item['href'] === $currentPage) {
        $currentLabel = $item['label'];
        break;
    }
}
$usuarioSesion = $_SESSION["usuario"] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Edmar Americas´s</title>
    <link rel="icon" type="image/png" href="../assets/img/logoM.png">

    <!-- Normalize V8.0.1 -->
    <link rel="stylesheet" href="../css/normalize.css">

    <!-- Bootstrap V4.3 -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <!-- Bootstrap Material Design V4.0 -->
    <link rel="stylesheet" href="../css/bootstrap-material-design.min.css">

    <!-- Font Awesome V5.9.0 -->
    <link rel="stylesheet" href="../css/all.css">

    <!-- Sweet Alerts V8.13.0 CSS file -->
    <link rel="stylesheet" href="../css/sweetalert2.min.css">

    <!-- Sweet Alert V8.13.0 JS file-->
    <script src="../js/sweetalert2.min.js"></script>

    <!-- jQuery Custom Content Scroller V3.1.5 -->
    <link rel="stylesheet" href="../css/jquery.mCustomScrollbar.css">

    <!-- General Styles -->
    <link rel="stylesheet" href="../css/style.css">
    <script>
        fetch("../controllers/session_check.php")
            .then(response => response.json())
            .then(data => {
                if (!data.auth) {
                    window.location.href = "../index.html?error=Debes%20iniciar%20sesi%C3%B3n";
                }
            })
            .catch(error => console.error("Error verificando sesion:", error));
    </script>
</head>
<body>
    <main class="full-box main-container">
        <section class="full-box nav-lateral">
            <div class="full-box nav-lateral-bg show-nav-lateral"></div>
            <div class="full-box nav-lateral-content">
                <figure class="full-box nav-lateral-avatar">
                    <i class="far fa-times-circle show-nav-lateral"></i>
                    <img src="../assets/img/logoM.png" class="img-fluid" alt="Edmar Americas">
                    <figcaption class="roboto-medium text-center">
                        Edmar Americas´s <br><small class="roboto-condensed-light">Administracion</small>
                    </figcaption>
                </figure>

                <div class="full-box nav-lateral-bar"></div>

                <nav class="full-box nav-lateral-menu">
                    <ul>
                        <?php foreach ($menuItems as $item): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($item['href']); ?>" class="<?php echo $currentPage === $item['href'] ? 'active' : ''; ?>">
                                    <i class="<?php echo htmlspecialchars($item['icon']); ?> fa-fw"></i>
                                    <span><?php echo htmlspecialchars($item['label']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </section>

        <section class="full-box page-content">
            <nav class="full-box navbar-info">
                <div class="navbar-info-left">
                    <a href="#" class="show-nav-lateral navbar-menu-toggle" title="Mostrar menu">
                        <i class="fas fa-bars"></i>
                    </a>
                    <div class="navbar-page-title">
                        <span><?php echo htmlspecialchars($currentLabel); ?></span>
                        <small>Panel administrativo</small>
                    </div>
                </div>

                <div class="navbar-info-actions">
                    <span class="navbar-user"><?php echo htmlspecialchars($usuarioSesion); ?></span>
                    <a href="user-update.php" title="Mi cuenta">
                        <i class="fas fa-user-cog"></i>
                    </a>
                    <a href="#" class="btn-exit-system" title="Cerrar sesion">
                        <i class="fas fa-power-off"></i>
                    </a>
                </div>
            </nav>
