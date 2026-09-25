<?php

require_once __DIR__ . '/includes/admin_layout.php';

exigirLogin();

$pdo = db();
$usuario = usuarioActual();
$error = '';
$editar = null;

if (isset($_GET['eliminar'])) {
    if (!esAdministrador($usuario['rol'] ?? '')) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'Solo un Administrador puede eliminar autores.';

        adminRedirect('autores.php');
    }

    try {
        $pdo->prepare("
            DELETE FROM autores
            WHERE id = ?
        ")->execute([
            (int)$_GET['eliminar']
        ]);

        $_SESSION['alerta_tipo'] = 'success';
        $_SESSION['alerta_mensaje'] =
            'Autor eliminado.';

    } catch (Throwable $e) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'No se puede eliminar un autor que esté siendo usado.';
    }

    adminRedirect('autores.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nombres = trim($_POST['nombres'] ?? '');
    $apPaterno = trim($_POST['ap_paterno'] ?? '');
    $apMaterno = trim($_POST['ap_materno'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $esNickname = isset($_POST['es_nickname']) ? 1 : 0;

    if ($nombres === '') {
        $error = 'El nombre es obligatorio.';
    } else {
        try {
            if ($id) {
                $pdo->prepare("
                    UPDATE autores
                    SET
                        nombres = ?,
                        ap_paterno = ?,
                        ap_materno = ?,
                        nickname = ?,
                        es_nickname = ?
                    WHERE id = ?
                ")->execute([
                    $nombres,
                    $apPaterno,
                    $apMaterno,
                    $nickname ?: null,
                    $esNickname,
                    $id
                ]);
            } else {
                $pdo->prepare("
                    INSERT INTO autores
                    (
                        nombres,
                        ap_paterno,
                        ap_materno,
                        nickname,
                        es_nickname
                    )
                    VALUES
                    (?, ?, ?, ?, ?)
                ")->execute([
                    $nombres,
                    $apPaterno,
                    $apMaterno,
                    $nickname ?: null,
                    $esNickname
                ]);
            }

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Autor guardado correctamente.';

            adminRedirect('autores.php');

        } catch (Throwable $e) {
            $error =
                'No se pudo guardar: ' .
                $e->getMessage();
        }
    }
}

if (isset($_GET['editar'])) {
    $st = $pdo->prepare("
        SELECT *
        FROM autores
        WHERE id = ?
    ");

    $st->execute([
        (int)$_GET['editar']
    ]);

    $editar = $st->fetch();
}

$filas = $pdo->query("
    SELECT *
    FROM autores
    ORDER BY
        nombres,
        ap_paterno
")->fetchAll();

adminHeader('Autores');
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
        ? 'Editar autor'
        : 'Nuevo autor'
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

<?php
$campos = [
    'nombres' => 'Nombres',
    'ap_paterno' => 'Apellido paterno',
    'ap_materno' => 'Apellido materno',
    'nickname' => 'Nickname'
];
?>

<?php foreach ($campos as $campo => $label): ?>

<div class="mb-3">

<label class="form-label">
    <?= e($label) ?>
</label>

<input
    class="form-control"
    name="<?= e($campo) ?>"
    <?= $campo === 'nombres'
        ? 'required'
        : ''
    ?>
    value="<?= e($editar[$campo] ?? '') ?>"
>

</div>

<?php endforeach; ?>


<div class="form-check mb-3">

<input
    class="form-check-input"
    type="checkbox"
    name="es_nickname"
    id="es_nickname"
    <?= !empty($editar['es_nickname'])
        ? 'checked'
        : ''
    ?>
>

<label
    class="form-check-label"
    for="es_nickname"
>
    Mostrar nickname
</label>

</div>


<button class="btn btn-danger">
    Guardar
</button>

<a
    href="autores.php"
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
    Autores existentes
    (<?= count($filas) ?>)
</strong>

</div>

<div class="card-body table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Nickname</th>
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
            $r['ap_paterno'] .
            ' ' .
            $r['ap_materno']
        )
    ) ?>
</td>

<td>
    <?= e($r['nickname'] ?? '') ?>
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
    esAdministrador(
        $usuario['rol']
        ?? ''
    )
): ?>

<a
    class="
        btn
        btn-sm
        btn-danger
    "
    href="?eliminar=<?= (int)$r['id'] ?>"
    onclick="return confirm('¿Eliminar este autor?')"
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
