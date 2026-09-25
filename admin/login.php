<?php

require_once __DIR__ . '/includes/sesion.php';

if (usuarioAutenticado()) {
    adminRedirect('index.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Completa tu correo y contraseña.';
    } else {
        $st = db()->prepare("
            SELECT
                id,
                nombres,
                ap_paterno,
                ap_materno,
                email,
                password_hash,
                rol
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ");

        $st->execute([$email]);

        $u = $st->fetch();

        if (
            $u &&
            password_verify(
                $password,
                $u['password_hash']
            )
        ) {
            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id' => (int)$u['id'],
                'nombres' => $u['nombres'],
                'ap_paterno' => $u['ap_paterno'],
                'ap_materno' => $u['ap_materno'],
                'email' => $u['email'],
                'rol' => $u['rol'],
            ];

            adminRedirect('index.php');
        }

        $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width,initial-scale=1"
    >

    <title>
        Acceso | <?= e(APP_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= ADMIN_URL ?>/assets/css/adminlte.min.css"
    >
</head>

<body class="login-page bg-body-secondary">

<main class="login-box">

    <div class="text-center mb-3">

        <div
            class="mx-auto mb-2"
            style="
                width:76px;
                height:76px;
                border-radius:50%;
                background:#df0a11;
                color:#fff;
                display:grid;
                place-items:center;
                font-weight:800;
                font-size:24px
            "
        >
            D&D
        </div>

        <h2 class="fw-bold">
            Diálogo y Desarrollo
        </h2>

        <p class="text-secondary">
            Panel de administración
        </p>

    </div>


    <div class="card shadow">

        <div class="card-body login-card-body">

            <p class="login-box-msg">
                Inicia sesión para gestionar el sitio
            </p>

            <?php if ($error): ?>

                <div class="alert alert-danger py-2">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <form method="post">

                <div class="input-group mb-3">

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Correo electrónico"
                        required
                        value="<?= e($email) ?>"
                    >

                    <div class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </div>

                </div>


                <div class="input-group mb-3">

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Contraseña"
                        required
                    >

                    <div class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </div>

                </div>


                <div class="d-grid">

                    <button class="btn btn-danger">
                        Iniciar sesión
                    </button>

                </div>

                <!-- SOLO SE AGREGA ESTE ENLACE -->
                <div class="text-center mt-3">
                    <a href="<?= ADMIN_URL ?>/forgot-password.php">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

            </form>

        </div>

    </div>

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

</body>
</html>
