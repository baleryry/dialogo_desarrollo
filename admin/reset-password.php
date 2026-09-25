<?php

require_once __DIR__ . '/includes/sesion.php';

if (usuarioAutenticado()) {
    adminRedirect('index.php');
}

$token = trim(
    $_GET['token']
    ?? $_POST['token']
    ?? ''
);

$error = '';
$mensaje = '';
$solicitud = null;

if ($token !== '') {

    try {

        $tokenHash = hash(
            'sha256',
            $token
        );

        $st = db()->prepare("
            SELECT
                pr.id,
                pr.usuario_id,
                pr.expires_at,
                u.email
            FROM password_resets pr
            INNER JOIN usuarios u
                ON u.id = pr.usuario_id
            WHERE pr.token_hash = ?
              AND pr.used_at IS NULL
              AND pr.expires_at > NOW()
            LIMIT 1
        ");

        $st->execute([
            $tokenHash
        ]);

        $solicitud = $st->fetch();

    } catch (Throwable $e) {

        $error =
            'No se pudo validar el enlace de recuperación.';
    }
}


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    $error === ''
) {

    if (!$solicitud) {

        $error =
            'El enlace es inválido, venció o ya fue utilizado.';

    } else {

        $password =
            $_POST['password']
            ?? '';

        $confirmar =
            $_POST['confirmar_password']
            ?? '';

        if (strlen($password) < 8) {

            $error =
                'La contraseña debe tener al menos 8 caracteres.';

        } elseif ($password !== $confirmar) {

            $error =
                'Las contraseñas no coinciden.';

        } else {

            try {

                db()->beginTransaction();

                /*
                 * Nueva contraseña almacenada mediante hash.
                 */
                $nuevoHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $up = db()->prepare("
                    UPDATE usuarios
                    SET password_hash = ?
                    WHERE id = ?
                ");

                $up->execute([
                    $nuevoHash,
                    (int)$solicitud['usuario_id']
                ]);

                /*
                 * Token usado: no puede reutilizarse.
                 */
                $used = db()->prepare("
                    UPDATE password_resets
                    SET used_at = NOW()
                    WHERE id = ?
                ");

                $used->execute([
                    (int)$solicitud['id']
                ]);

                db()->commit();

                $mensaje =
                    'Contraseña cambiada correctamente. Ya puedes iniciar sesión.';

                $solicitud = null;

            } catch (Throwable $e) {

                if (db()->inTransaction()) {
                    db()->rollBack();
                }

                $error =
                    'No se pudo actualizar la contraseña.';
            }
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
        Cambiar contraseña | <?= e(APP_NAME) ?>
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
            Nueva contraseña
        </h2>

        <p class="text-secondary">
            Panel de administración
        </p>

    </div>


    <div class="card shadow">

        <div class="card-body login-card-body">


            <?php if ($error): ?>

                <div class="alert alert-danger py-2">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($mensaje): ?>

                <div class="alert alert-success py-2">
                    <?= e($mensaje) ?>
                </div>

                <div class="d-grid">

                    <a
                        href="<?= ADMIN_URL ?>/login.php"
                        class="btn btn-danger"
                    >
                        Iniciar sesión
                    </a>

                </div>

            <?php elseif ($solicitud): ?>

                <p class="login-box-msg">

                    Nueva contraseña para

                    <strong>
                        <?= e($solicitud['email']) ?>
                    </strong>

                </p>


                <form method="post">

                    <input
                        type="hidden"
                        name="token"
                        value="<?= e($token) ?>"
                    >


                    <div class="input-group mb-3">

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Nueva contraseña"
                            minlength="8"
                            required
                        >

                        <div class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </div>

                    </div>


                    <div class="input-group mb-3">

                        <input
                            type="password"
                            name="confirmar_password"
                            class="form-control"
                            placeholder="Confirmar contraseña"
                            minlength="8"
                            required
                        >

                        <div class="input-group-text">
                            <i class="bi bi-shield-lock"></i>
                        </div>

                    </div>


                    <div class="d-grid">

                        <button class="btn btn-danger">
                            Cambiar contraseña
                        </button>

                    </div>

                </form>

            <?php else: ?>

                <div class="alert alert-warning">

                    El enlace no existe, venció
                    o ya fue utilizado.

                </div>

                <div class="d-grid">

                    <a
                        href="<?= ADMIN_URL ?>/forgot-password.php"
                        class="btn btn-dark"
                    >
                        Solicitar nuevo enlace
                    </a>

                </div>

            <?php endif; ?>


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
