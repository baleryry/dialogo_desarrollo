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
            'Solo un Administrador puede eliminar videos.';

        adminRedirect('videos.php');
    }

    $pdo->prepare("
        DELETE FROM videos
        WHERE id = ?
    ")->execute([
        (int)$_GET['eliminar']
    ]);

    $_SESSION['alerta_tipo'] = 'success';
    $_SESSION['alerta_mensaje'] =
        'Video eliminado.';

    adminRedirect('videos.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $url = trim($_POST['url_embed'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? date('Y-m-d');

    if ($titulo === '' || $url === '') {
        $error =
            'Título y URL del video son obligatorios.';
    } else {
        try {
            if ($id) {
                $pdo->prepare("
                    UPDATE videos
                    SET
                        titulo = ?,
                        url_embed = ?,
                        fecha_publicacion = ?,
                        usuario_id = ?
                    WHERE id = ?
                ")->execute([
                    $titulo,
                    $url,
                    $fecha,
                    (int)$usuario['id'],
                    $id
                ]);
            } else {
                $pdo->prepare("
                    INSERT INTO videos
                    (
                        titulo,
                        url_embed,
                        fecha_publicacion,
                        usuario_id
                    )
                    VALUES
                    (?, ?, ?, ?)
                ")->execute([
                    $titulo,
                    $url,
                    $fecha,
                    (int)$usuario['id']
                ]);
            }

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Video guardado correctamente.';

            adminRedirect('videos.php');

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
        FROM videos
        WHERE id = ?
    ");

    $st->execute([
        (int)$_GET['editar']
    ]);

    $editar = $st->fetch();
}

$filas = $pdo->query("
    SELECT *
    FROM videos
    ORDER BY
        fecha_publicacion DESC,
        id DESC
")->fetchAll();

adminHeader('Videos');
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
        ? 'Editar video'
        : 'Nuevo video'
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
    Título
</label>

<input
    class="form-control"
    name="titulo"
    required
    value="<?= e($editar['titulo'] ?? '') ?>"
>

</div>


<div class="mb-3">

<label class="form-label">
    URL / Embed
</label>

<input
    class="form-control"
    name="url_embed"
    required
    placeholder="https://..."
    value="<?= e($editar['url_embed'] ?? '') ?>"
>

</div>


<div class="mb-3">

<label class="form-label">
    Fecha
</label>

<input
    class="form-control"
    type="date"
    name="fecha_publicacion"
    value="<?= e(
        substr(
            (string)(
                $editar['fecha_publicacion']
                ?? date('Y-m-d')
            ),
            0,
            10
        )
    ) ?>"
>

</div>


<button class="btn btn-danger">
    Guardar
</button>

<a
    href="videos.php"
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
    Videos existentes
    (<?= count($filas) ?>)
</strong>

</div>

<div class="card-body table-responsive">

<table
    class="
        table
        table-hover
        align-middle
    "
>

<thead>

<tr>
    <th>ID</th>
    <th>Título</th>
    <th>Fecha</th>
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
    <?= e($r['titulo']) ?>
</td>

<td>
    <?= e(
        substr(
            (string)$r['fecha_publicacion'],
            0,
            10
        )
    ) ?>
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
    onclick="return confirm('¿Eliminar este video?')"
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
