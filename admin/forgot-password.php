<?php

require_once __DIR__ . '/includes/sesion.php';

/* AGREGADO: sincroniza la hora de PHP y MySQL en Cusco/Perú */
date_default_timezone_set('America/Lima');
db()->exec("SET time_zone = '-05:00'");

if (usuarioAutenticado()) {
    adminRedirect('index.php');
}

$email = '';
$error = '';
$mensaje = '';
$enlaceLocal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Ingresa un correo electrónico válido.';

    } else {

        try {

            $st = db()->prepare("
                SELECT id, email
                FROM usuarios
                WHERE email = ?
                LIMIT 1
            ");

            $st->execute([$email]);
            $usuario = $st->fetch();

            /*
             * Mensaje genérico:
             * no revela si un correo existe o no.
             */
            $mensaje =
                'Si el correo está registrado, se generó un enlace de recuperación válido por 30 minutos.';

            if ($usuario) {

                /*
                 * Elimina enlaces anteriores del mismo usuario.
                 */
                $del = db()->prepare("
                    DELETE FROM password_resets
                    WHERE usuario_id = ?
                ");

                $del->execute([
                    (int)$usuario['id']
                ]);

                /*
                 * Token criptográficamente seguro.
                 */
                $token = bin2hex(random_bytes(32));

                /*
                 * En la base de datos NO guardamos el token real.
                 */
                $tokenHash = hash(
                    'sha256',
                    $token
                );

                $expira = date(
                    'Y-m-d H:i:s',
                    time() + 1800
                );

                $ins = db()->prepare("
                    INSERT INTO password_resets
                    (
                        usuario_id,
                        token_hash,
                        expires_at,
                        created_at
                    )
                    VALUES
                    (?, ?, ?, NOW())
                ");

                $ins->execute([
                    (int)$usuario['id'],
                    $tokenHash,
                    $expira
                ]);

                /*
                 * Como el proyecto está en XAMPP local,
                 * mostramos el enlace para la demostración.
                 * En producción este enlace se envía por correo.
                 */
                $enlaceLocal =
                    ADMIN_URL .
                    '/reset-password.php?token=' .
                    urlencode($token);
            }

        } catch (Throwable $e) {

            $error =
                'No se pudo iniciar la recuperación. ' .
                'Verifica que la tabla password_resets exista.';
        }
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
        Recuperar contraseña | <?= e(APP_NAME) ?>
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
            Recuperar contraseña
        </h2>

        <p class="text-secondary">
            Panel de administración
        </p>

    </div>


    <div class="card shadow">

        <div class="card-body login-card-body">

            <p class="login-box-msg">
                Ingresa el correo asociado a tu cuenta
            </p>


            <?php if ($error): ?>

                <div class="alert alert-danger py-2">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($mensaje): ?>

                <div class="alert alert-success py-2">
                    <?= e($mensaje) ?>
                </div>

            <?php endif; ?>


            <?php if ($enlaceLocal): ?>

                <div class="alert alert-warning">

                    <strong>
                        Modo local XAMPP
                    </strong>

                    <p class="mt-2 mb-2">
                        En un servidor real este enlace llegaría por correo.
                        Para la demostración local puedes usarlo directamente.
                    </p>

                    <a
                        href="<?= e($enlaceLocal) ?>"
                        class="btn btn-dark btn-sm"
                    >
                        Cambiar mi contraseña
                    </a>

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


                <div class="d-grid">

                    <button class="btn btn-danger">
                        Recuperar contraseña
                    </button>

                </div>

            </form>


            <div class="text-center mt-3">

                <a href="<?= ADMIN_URL ?>/login.php">
                    Volver al inicio de sesión
                </a>

            </div>

        </div>

    </div>

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

</body>
</html>
