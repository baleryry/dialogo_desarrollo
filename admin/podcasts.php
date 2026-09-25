<?php

require_once __DIR__ . '/includes/admin_layout.php';

exigirLogin();

$pdo = db();
$usuario = usuarioActual();
$error = '';
$editar = null;

$columnas = $pdo
    ->query("SHOW COLUMNS FROM podcasts")
    ->fetchAll(PDO::FETCH_COLUMN);

$tieneDescripcion =
    in_array('descripcion', $columnas, true);

$tienePortada =
    in_array('portada', $columnas, true);

$tienePlataforma =
    in_array('plataforma', $columnas, true);

$tieneEstado =
    in_array('estado', $columnas, true);

$tieneDestacado =
    in_array('es_destacado', $columnas, true);


if (isset($_GET['eliminar'])) {
    if (!esAdministrador($usuario['rol'] ?? '')) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'Solo un Administrador puede eliminar podcasts.';

        adminRedirect('podcasts.php');
    }

    $id = (int)$_GET['eliminar'];

    $portada = null;

    if ($tienePortada) {
        $st = $pdo->prepare("
            SELECT portada
            FROM podcasts
            WHERE id = ?
        ");

        $st->execute([$id]);

        $portada =
            $st->fetchColumn()
            ?: null;
    }

    $pdo->prepare("
        DELETE FROM podcasts
        WHERE id = ?
    ")->execute([$id]);

    eliminarArchivoSubido(
        'podcasts',
        $portada
    );

    $_SESSION['alerta_tipo'] = 'success';
    $_SESSION['alerta_mensaje'] =
        'Podcast eliminado.';

    adminRedirect('podcasts.php');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $url = trim($_POST['url_embed'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? date('Y-m-d');

    if ($titulo === '' || $url === '') {
        $error =
            'Título y URL del podcast son obligatorios.';
    } else {
        try {
            $datos = [
                'titulo' => $titulo,
                'url_embed' => $url,
                'fecha_publicacion' => $fecha,
                'usuario_id' => (int)$usuario['id'],
            ];

            $portadaActual = null;
            $nuevaPortada = null;

            if ($tieneDescripcion) {
                $datos['descripcion'] =
                    trim(
                        $_POST['descripcion']
                        ?? ''
                    );
            }

            if ($tienePlataforma) {
                $plataforma =
                    $_POST['plataforma']
                    ?? 'otro';

                if (
                    !in_array(
                        $plataforma,
                        [
                            'spotify',
                            'youtube',
                            'soundcloud',
                            'otro'
                        ],
                        true
                    )
                ) {
                    $plataforma = 'otro';
                }

                $datos['plataforma'] =
                    $plataforma;
            }

            if ($tieneEstado) {
                $estado =
                    $_POST['estado']
                    ?? 'publicado';

                if (
                    !in_array(
                        $estado,
                        [
                            'borrador',
                            'publicado',
                            'archivado'
                        ],
                        true
                    )
                ) {
                    $estado = 'publicado';
                }

                $datos['estado'] =
                    $estado;
            }

            if ($tieneDestacado) {
                $datos['es_destacado'] =
                    isset($_POST['es_destacado'])
                    ? 1
                    : 0;
            }

            if ($tienePortada) {
                if ($id) {
                    $st = $pdo->prepare("
                        SELECT portada
                        FROM podcasts
                        WHERE id = ?
                    ");

                    $st->execute([$id]);

                    $portadaActual =
                        $st->fetchColumn()
                        ?: null;
                }

                $nuevaPortada =
                    subirArchivo(
                        'portada',
                        'podcasts',
                        [
                            'jpg',
                            'jpeg',
                            'png',
                            'webp'
                        ],
                        8 * 1024 * 1024
                    );

                $datos['portada'] =
                    $nuevaPortada
                    ?: $portadaActual;
            }

            if ($id) {
                $sets = [];
                $params = [];

                foreach ($datos as $campo => $valor) {
                    $sets[] = "`$campo` = ?";
                    $params[] = $valor;
                }

                $params[] = $id;

                $sql =
                    "UPDATE podcasts SET " .
                    implode(', ', $sets) .
                    " WHERE id = ?";

                $pdo
                    ->prepare($sql)
                    ->execute($params);

            } else {
                $campos =
                    array_keys($datos);

                $sql =
                    "INSERT INTO podcasts (" .
                    implode(
                        ', ',
                        array_map(
                            fn($c) => "`$c`",
                            $campos
                        )
                    ) .
                    ") VALUES (" .
                    implode(
                        ', ',
                        array_fill(
                            0,
                            count($campos),
                            '?'
                        )
                    ) .
                    ")";

                $pdo
                    ->prepare($sql)
                    ->execute(
                        array_values($datos)
                    );
            }

            if (
                $nuevaPortada &&
                $portadaActual
            ) {
                eliminarArchivoSubido(
                    'podcasts',
                    $portadaActual
                );
            }

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Podcast guardado correctamente.';

            adminRedirect('podcasts.php');

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
        FROM podcasts
        WHERE id = ?
    ");

    $st->execute([
        (int)$_GET['editar']
    ]);

    $editar = $st->fetch();
}


$filas = $pdo->query("
    SELECT *
    FROM podcasts
    ORDER BY
        fecha_publicacion DESC,
        id DESC
")->fetchAll();


adminHeader('Podcasts');
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
        ? 'Editar podcast'
        : 'Nuevo podcast'
    ?>
</strong>

</div>

<div class="card-body">

<form
    method="post"
    enctype="multipart/form-data"
>

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


<?php if ($tieneDescripcion): ?>

<div class="mb-3">

<label class="form-label">
    Descripción
</label>

<textarea
    class="form-control"
    name="descripcion"
    rows="4"
><?= e($editar['descripcion'] ?? '') ?></textarea>

</div>

<?php endif; ?>


<?php if ($tienePortada): ?>

<div class="mb-3">

<label class="form-label">
    Portada
</label>

<input
    class="form-control"
    type="file"
    name="portada"
    accept=".jpg,.jpeg,.png,.webp"
>

<?php if (!empty($editar['portada'])): ?>

<img
    class="preview-admin mt-2"
    src="<?= ADMIN_URL ?>/uploads/podcasts/<?= e($editar['portada']) ?>"
    alt=""
>

<?php endif; ?>

</div>

<?php endif; ?>


<?php if ($tienePlataforma): ?>

<div class="mb-3">

<label class="form-label">
    Plataforma
</label>

<select
    class="form-select"
    name="plataforma"
>

<?php
$plataformas = [
    'spotify',
    'youtube',
    'soundcloud',
    'otro'
];
?>

<?php foreach ($plataformas as $p): ?>

<option
    value="<?= e($p) ?>"
    <?= ($editar['plataforma'] ?? 'otro') === $p
        ? 'selected'
        : ''
    ?>
>
    <?= e(ucfirst($p)) ?>
</option>

<?php endforeach; ?>

</select>

</div>

<?php endif; ?>


<?php if ($tieneEstado): ?>

<div class="mb-3">

<label class="form-label">
    Estado
</label>

<select
    class="form-select"
    name="estado"
>

<?php
$estados = [
    'borrador',
    'publicado',
    'archivado'
];
?>

<?php foreach ($estados as $estado): ?>

<option
    value="<?= e($estado) ?>"
    <?= ($editar['estado'] ?? 'publicado') === $estado
        ? 'selected'
        : ''
    ?>
>
    <?= e(ucfirst($estado)) ?>
</option>

<?php endforeach; ?>

</select>

</div>

<?php endif; ?>


<?php if ($tieneDestacado): ?>

<div class="form-check mb-3">

<input
    class="form-check-input"
    type="checkbox"
    name="es_destacado"
    id="podcast_destacado"
    <?= !empty($editar['es_destacado'])
        ? 'checked'
        : ''
    ?>
>

<label
    class="form-check-label"
    for="podcast_destacado"
>
    Podcast destacado
</label>

</div>

<?php endif; ?>


<button class="btn btn-danger">
    Guardar
</button>

<a
    href="podcasts.php"
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
    Podcasts existentes
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
    <?php if ($tienePortada): ?>
        <th>Portada</th>
    <?php endif; ?>
    <th>Título</th>
    <th>Fecha</th>
    <?php if ($tieneEstado): ?>
        <th>Estado</th>
    <?php endif; ?>
    <th></th>
</tr>

</thead>

<tbody>

<?php foreach ($filas as $r): ?>

<tr>

<td>
    <?= (int)$r['id'] ?>
</td>

<?php if ($tienePortada): ?>

<td>

<?php if (!empty($r['portada'])): ?>

<img
    class="preview-admin"
    src="<?= ADMIN_URL ?>/uploads/podcasts/<?= e($r['portada']) ?>"
    alt=""
>

<?php else: ?>

—

<?php endif; ?>

</td>

<?php endif; ?>

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

<?php if ($tieneEstado): ?>

<td>
    <?= e($r['estado'] ?? '') ?>
</td>

<?php endif; ?>

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
    onclick="return confirm('¿Eliminar este podcast?')"
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
