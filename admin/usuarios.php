<?php

require_once __DIR__ . '/includes/admin_layout.php';

exigirAdmin();

$pdo = db();
$actual = usuarioActual();
$error = '';
$editar = null;

if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];

    if ($id === (int)$actual['id']) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'No puedes eliminar tu propia cuenta.';

    } else {
        try {
            $pdo->prepare("
                DELETE FROM usuarios
                WHERE id = ?
            ")->execute([$id]);

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Usuario eliminado.';

        } catch (Throwable $e) {
            $_SESSION['alerta_tipo'] = 'danger';
            $_SESSION['alerta_mensaje'] =
                'No se pudo eliminar. Puede tener contenido asociado.';
        }
    }

    adminRedirect('usuarios.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nombres = trim($_POST['nombres'] ?? '');
    $apPaterno = trim($_POST['ap_paterno'] ?? '');
    $apMaterno = trim($_POST['ap_materno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = trim($_POST['rol'] ?? 'redactor');
    $password = $_POST['password'] ?? '';

    if ($nombres === '' || $email === '') {
        $error =
            'Nombres y correo son obligatorios.';
    } else {
        try {
            if ($id) {
                if ($password !== '') {
                    $pdo->prepare("
                        UPDATE usuarios
                        SET
                            nombres = ?,
                            ap_paterno = ?,
                            ap_materno = ?,
                            email = ?,
                            rol = ?,
                            password_hash = ?
                        WHERE id = ?
                    ")->execute([
                        $nombres,
                        $apPaterno,
                        $apMaterno,
                        $email,
                        $rol,
                        password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        ),
                        $id
                    ]);

                } else {
                    $pdo->prepare("
                        UPDATE usuarios
                        SET
                            nombres = ?,
                            ap_paterno = ?,
                            ap_materno = ?,
                            email = ?,
                            rol = ?
                        WHERE id = ?
                    ")->execute([
                        $nombres,
                        $apPaterno,
                        $apMaterno,
                        $email,
                        $rol,
                        $id
                    ]);
                }

            } else {
                if ($password === '') {
                    throw new RuntimeException(
                        'La contraseña es obligatoria para un usuario nuevo.'
                    );
                }

                $pdo->prepare("
                    INSERT INTO usuarios
                    (
                        nombres,
                        ap_paterno,
                        ap_materno,
                        email,
                        password_hash,
                        rol
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?)
                ")->execute([
                    $nombres,
                    $apPaterno,
                    $apMaterno,
                    $email,
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    ),
                    $rol
                ]);
            }

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Usuario guardado correctamente.';

            adminRedirect('usuarios.php');

        } catch (Throwable $e) {
            $error =
                'No se pudo guardar: ' .
                $e->getMessage();
        }
    }
}

if (isset($_GET['editar'])) {
    $st = $pdo->prepare("
        SELECT
            id,
            nombres,
            ap_paterno,
            ap_materno,
            email,
            rol
        FROM usuarios
        WHERE id = ?
    ");

    $st->execute([
        (int)$_GET['editar']
    ]);

    $editar = $st->fetch();
}

$filas = $pdo->query("
    SELECT
        id,
        nombres,
        ap_paterno,
        ap_materno,
        email,
        rol,
        created_at
    FROM usuarios
    ORDER BY id DESC
")->fetchAll();

adminHeader('Usuarios');
?>

<?php if ($error): ?>

<div class="alert alert-danger">
    <?= e($error) ?>
</div>

<?php endif; ?>


<div class="row g-4">

<div class="col-lg-5">

<div class="card shadow-sm">

<div class="card-header">

<strong>
    <?= $editar
        ? 'Editar usuario'
        : 'Nuevo usuario'
    ?>
</strong>

</div>

<div class="card-body">

<form method="post">

<input
    type="hidden"
    name="id"
    value="<?= e($editar['id'] ?? '') ?>"
>


<div class="mb-3">

<label class="form-label">
    Nombres
</label>

<input
    class="form-control"
    name="nombres"
    required
    value="<?= e($editar['nombres'] ?? '') ?>"
>

</div>


<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">
    Apellido paterno
</label>

<input
    class="form-control"
    name="ap_paterno"
    value="<?= e($editar['ap_paterno'] ?? '') ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Apellido materno
</label>

<input
    class="form-control"
    name="ap_materno"
    value="<?= e($editar['ap_materno'] ?? '') ?>"
>

</div>

</div>


<div class="mb-3">

<label class="form-label">
    Correo
</label>

<input
    class="form-control"
    type="email"
    name="email"
    required
    value="<?= e($editar['email'] ?? '') ?>"
>

</div>


<div class="mb-3">

<label class="form-label">
    Rol
</label>

<input
    class="form-control"
    name="rol"
    placeholder="admin / redactor"
    value="<?= e($editar['rol'] ?? 'redactor') ?>"
>

<small class="text-secondary">
    Usa el mismo formato de rol que ya tienes en tu base.
</small>

</div>


<div class="mb-3">

<label class="form-label">
    Contraseña
    <?= $editar
        ? '(vacía = conservar)'
        : ''
    ?>
</label>

<input
    class="form-control"
    type="password"
    name="password"
    <?= $editar ? '' : 'required' ?>
>

</div>


<button class="btn btn-danger">
    Guardar
</button>

<a
    href="usuarios.php"
    class="btn btn-secondary"
>
    Limpiar
</a>

</form>

</div>

</div>

</div>


<div class="col-lg-7">

<div class="card shadow-sm">

<div class="card-header">

<strong>
    Usuarios existentes
    (<?= count($filas) ?>)
</strong>

</div>

<div class="card-body table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Rol</th>
    <th></th>
</tr>

</thead>

<tbody>

<?php foreach ($filas as $r): ?>

<tr>

<td>
    <?= (int)$r['id'] ?>
</td>

<td>
    <?= e(
        trim(
            $r['nombres'] .
            ' ' .
            $r['ap_paterno']
        )
    ) ?>
</td>

<td>
    <?= e($r['email']) ?>
</td>

<td>
    <span class="badge text-bg-secondary">
        <?= e($r['rol']) ?>
    </span>
</td>

<td class="table-actions">

<a
    class="
        btn
        btn-sm
        btn-primary
    "
    href="?editar=<?= (int)$r['id'] ?>"
>
    Editar
</a>

<?php if (
    (int)$r['id']
    !==
    (int)$actual['id']
): ?>

<a
    class="
        btn
        btn-sm
        btn-danger
    "
    href="?eliminar=<?= (int)$r['id'] ?>"
    onclick="return confirm('¿Eliminar este usuario?')"
>
    Eliminar
</a>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<?php adminFooter(); ?>
