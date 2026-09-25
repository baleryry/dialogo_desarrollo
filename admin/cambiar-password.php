<?php

require_once __DIR__ . '/includes/admin_layout.php';

exigirLogin();

$pdo = db();
$u = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    $st = $pdo->prepare("
        SELECT password_hash
        FROM usuarios
        WHERE id = ?
    ");

    $st->execute([(int)$u['id']]);

    $hash = $st->fetchColumn();

    if (!$hash || !password_verify($actual, $hash)) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'La contraseña actual no es correcta.';

    } elseif (strlen($nueva) < 6) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'La nueva contraseña debe tener al menos 6 caracteres.';

    } elseif ($nueva !== $confirmar) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'Las contraseñas nuevas no coinciden.';

    } else {
        $pdo->prepare("
            UPDATE usuarios
            SET password_hash = ?
            WHERE id = ?
        ")->execute([
            password_hash(
                $nueva,
                PASSWORD_DEFAULT
            ),
            (int)$u['id']
        ]);

        $_SESSION['alerta_tipo'] = 'success';
        $_SESSION['alerta_mensaje'] =
            'Contraseña actualizada correctamente.';
    }

    adminRedirect('cambiar-password.php');
}

adminHeader('Cambiar contraseña');
?>

<div class="row">

    <div class="col-lg-6">

        <div class="card shadow-sm">

            <div class="card-body">

                <form method="post">

                    <div class="mb-3">

                        <label class="form-label">
                            Contraseña actual
                        </label>

                        <input
                            type="password"
                            name="actual"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Nueva contraseña
                        </label>

                        <input
                            type="password"
                            name="nueva"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Confirmar nueva contraseña
                        </label>

                        <input
                            type="password"
                            name="confirmar"
                            class="form-control"
                            required
                        >

                    </div>

                    <button class="btn btn-danger">
                        Guardar
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<?php adminFooter(); ?>
