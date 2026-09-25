<?php

require_once __DIR__ . '/sesion.php';

function adminHeader(
    string $titulo = 'Panel administrativo'
): void {
    $u = usuarioActual();

    $nombre = trim(
        ($u['nombres'] ?? '') .
        ' ' .
        ($u['ap_paterno'] ?? '')
    );

    if ($nombre === '') {
        $nombre = 'Usuario';
    }

    $rol = $u['rol'] ?? '';
    $actual = basename($_SERVER['PHP_SELF'] ?? '');

    $items = [
        ['index.php', 'bi-speedometer2', 'Dashboard'],
        ['reportajes.php', 'bi-newspaper', 'Reportajes'],
        ['noticias.php', 'bi-megaphone', 'Actualidad / Noticias'],
        ['boletines.php', 'bi-file-earmark-pdf', 'Boletines NTEP'],
        ['podcasts.php', 'bi-mic', 'Podcasts'],
        ['videos.php', 'bi-play-btn', 'Videos'],
        ['autores.php', 'bi-person-vcard', 'Autores'],
    ];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= e($titulo) ?> | <?= e(APP_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= ADMIN_URL ?>/assets/css/adminlte.min.css"
    >

    <style>
        .brand-dd {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-grid;
            place-items: center;
            background: #d71920;
            color: white;
            font-weight: 800;
            margin-right: .5rem;
        }

        .table-actions {
            white-space: nowrap;
        }

        .preview-admin {
            width: 68px;
            height: 52px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body
    class="
        layout-fixed
        sidebar-expand-lg
        bg-body-tertiary
    "
>

<div class="app-wrapper">

    <nav class="app-header navbar navbar-expand bg-body">

        <div class="container-fluid">

            <ul class="navbar-nav">

                <li class="nav-item">

                    <a
                        class="nav-link"
                        data-lte-toggle="sidebar"
                        href="#"
                        role="button"
                    >
                        <i class="bi bi-list"></i>
                    </a>

                </li>

                <li class="nav-item d-none d-md-block">

                    <a
                        href="<?= APP_URL ?>/index.php"
                        target="_blank"
                        class="nav-link"
                    >
                        Ver sitio público
                    </a>

                </li>

            </ul>

            <ul class="navbar-nav ms-auto">

                <li class="nav-item dropdown">

                    <a
                        class="nav-link dropdown-toggle"
                        href="#"
                        data-bs-toggle="dropdown"
                    >
                        <i
                            class="
                                bi
                                bi-person-circle
                                me-1
                            "
                        ></i>

                        <?= e($nombre) ?>
                    </a>

                    <div
                        class="
                            dropdown-menu
                            dropdown-menu-end
                        "
                    >

                        <span
                            class="
                                dropdown-item-text
                                small
                                text-secondary
                            "
                        >
                            Rol: <?= e($rol) ?>
                        </span>

                        <div class="dropdown-divider"></div>

                        <a
                            class="dropdown-item"
                            href="<?= ADMIN_URL ?>/cambiar-password.php"
                        >
                            <i class="bi bi-key me-2"></i>
                            Cambiar contraseña
                        </a>

                        <a
                            class="dropdown-item"
                            href="<?= APP_URL ?>/index.php"
                            target="_blank"
                        >
                            <i
                                class="
                                    bi
                                    bi-box-arrow-up-right
                                    me-2
                                "
                            ></i>
                            Ver sitio público
                        </a>

                        <div class="dropdown-divider"></div>

                        <a
                            class="dropdown-item"
                            href="<?= ADMIN_URL ?>/logout.php"
                        >
                            <i
                                class="
                                    bi
                                    bi-box-arrow-right
                                    me-2
                                "
                            ></i>
                            Cerrar sesión
                        </a>

                    </div>

                </li>

            </ul>

        </div>

    </nav>


    <aside
        class="
            app-sidebar
            bg-dark
            shadow
        "
        data-bs-theme="dark"
    >

        <div class="sidebar-brand">

            <a
                href="<?= ADMIN_URL ?>/index.php"
                class="
                    brand-link
                    text-decoration-none
                "
            >
                <span class="brand-dd">DD</span>
                <span class="brand-text fw-light">
                    D&D Admin
                </span>
            </a>

        </div>


        <div class="sidebar-wrapper">

            <nav class="mt-2">

                <ul
                    class="
                        nav
                        sidebar-menu
                        flex-column
                    "
                    data-lte-toggle="treeview"
                    role="navigation"
                >

                    <?php foreach ($items as [$archivo, $icono, $texto]): ?>

                        <li class="nav-item">

                            <a
                                href="<?= ADMIN_URL ?>/<?= e($archivo) ?>"
                                class="
                                    nav-link
                                    <?= $actual === $archivo
                                        ? 'active'
                                        : ''
                                    ?>
                                "
                            >
                                <i
                                    class="
                                        nav-icon
                                        bi
                                        <?= e($icono) ?>
                                    "
                                ></i>

                                <p>
                                    <?= e($texto) ?>
                                </p>
                            </a>

                        </li>

                    <?php endforeach; ?>


                    <?php if (esAdministrador($rol)): ?>

                        <li class="nav-item">

                            <a
                                href="<?= ADMIN_URL ?>/usuarios.php"
                                class="
                                    nav-link
                                    <?= $actual === 'usuarios.php'
                                        ? 'active'
                                        : ''
                                    ?>
                                "
                            >
                                <i
                                    class="
                                        nav-icon
                                        bi
                                        bi-people
                                    "
                                ></i>

                                <p>
                                    Usuarios
                                </p>
                            </a>

                        </li>

                    <?php endif; ?>


                    <li class="nav-item mt-2">

                        <a
                            href="<?= APP_URL ?>/index.php"
                            target="_blank"
                            class="nav-link"
                        >
                            <i
                                class="
                                    nav-icon
                                    bi
                                    bi-box-arrow-up-right
                                "
                            ></i>

                            <p>
                                Ver sitio público
                            </p>
                        </a>

                    </li>

                </ul>

            </nav>

        </div>

    </aside>


    <main class="app-main">

        <div class="app-content-header">

            <div class="container-fluid">

                <div class="row">

                    <div class="col-sm-8">

                        <h3 class="mb-0">
                            <?= e($titulo) ?>
                        </h3>

                    </div>

                </div>

            </div>

        </div>


        <div class="app-content">

            <div class="container-fluid">

                <?php
                if (!empty($_SESSION['alerta_mensaje'])) {
                    $tipo =
                        $_SESSION['alerta_tipo']
                        ?? 'info';

                    echo
                        '<div class="alert alert-' .
                        e($tipo) .
                        ' alert-dismissible fade show" role="alert">' .
                        e($_SESSION['alerta_mensaje']) .
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' .
                        '</div>';

                    unset(
                        $_SESSION['alerta_tipo'],
                        $_SESSION['alerta_mensaje']
                    );
                }
                ?>

<?php
}


function adminFooter(): void
{
?>
            </div>

        </div>

    </main>


    <footer class="app-footer">

        <strong>
            Diálogo y Desarrollo
        </strong>

        — Panel administrativo

    </footer>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

<script
    src="<?= ADMIN_URL ?>/assets/js/adminlte.min.js"
></script>

</body>
</html>
<?php
}
