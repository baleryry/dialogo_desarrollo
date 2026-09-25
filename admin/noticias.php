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
            'Solo un Administrador puede eliminar noticias.';

        adminRedirect('noticias.php');
    }

    $id = (int)$_GET['eliminar'];

    $st = $pdo->prepare("
        SELECT foto
        FROM noticias
        WHERE id = ?
    ");

    $st->execute([$id]);
    $foto = $st->fetchColumn();

    $pdo->prepare("
        DELETE FROM noticias
        WHERE id = ?
    ")->execute([$id]);

    eliminarArchivoSubido(
        'noticias',
        $foto ?: null
    );

    $_SESSION['alerta_tipo'] = 'success';
    $_SESSION['alerta_mensaje'] =
        'Noticia eliminada.';

    adminRedirect('noticias.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $link = trim($_POST['link_externo'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? date('Y-m-d');

    if ($titulo === '') {
        $error = 'El título es obligatorio.';
    } else {
        try {
            $fotoActual = null;

            if ($id) {
                $st = $pdo->prepare("
                    SELECT foto
                    FROM noticias
                    WHERE id = ?
                ");

                $st->execute([$id]);

                $fotoActual =
                    $st->fetchColumn()
                    ?: null;
            }

            $nuevaFoto = subirArchivo(
                'foto',
                'noticias',
                ['jpg', 'jpeg', 'png', 'webp'],
                8 * 1024 * 1024
            );

            $fotoFinal =
                $nuevaFoto
                ?: $fotoActual;

            if ($id) {
                $pdo->prepare("
                    UPDATE noticias
                    SET
                        titulo = ?,
                        foto = ?,
                        link_externo = ?,
                        fecha_publicacion = ?,
                        usuario_id = ?
                    WHERE id = ?
                ")->execute([
                    $titulo,
                    $fotoFinal,
                    $link ?: null,
                    $fecha,
                    (int)$usuario['id'],
                    $id
                ]);
            } else {
                $pdo->prepare("
                    INSERT INTO noticias
                    (
                        titulo,
                        foto,
                        link_externo,
                        fecha_publicacion,
                        usuario_id
                    )
                    VALUES
                    (?, ?, ?, ?, ?)
                ")->execute([
                    $titulo,
                    $fotoFinal,
                    $link ?: null,
                    $fecha,
                    (int)$usuario['id']
                ]);
            }

            if ($nuevaFoto && $fotoActual) {
                eliminarArchivoSubido(
                    'noticias',
                    $fotoActual
                );
            }

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Noticia guardada correctamente.';

            adminRedirect('noticias.php');

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
        FROM noticias
        WHERE id = ?
    ");

    $st->execute([
        (int)$_GET['editar']
    ]);

    $editar = $st->fetch();
}

$filas = $pdo->query("
    SELECT *
    FROM noticias
    ORDER BY
        fecha_publicacion DESC,
        id DESC
")->fetchAll();

adminHeader('Actualidad / Noticias');
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
                    ? 'Editar noticia'
                    : 'Nueva noticia'
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
                        Enlace externo
                    </label>

                    <input
                        class="form-control"
                        type="url"
                        name="link_externo"
                        placeholder="https://..."
                        value="<?= e($editar['link_externo'] ?? '') ?>"
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


                <div class="mb-3">

                    <label class="form-label">
                        Foto
                    </label>

                    <input
                        class="form-control"
                        type="file"
                        name="foto"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <?php if (!empty($editar['foto'])): ?>

                        <img
                            class="preview-admin mt-2"
                            src="<?= ADMIN_URL ?>/uploads/noticias/<?= e($editar['foto']) ?>"
                            alt=""
                        >

                    <?php endif; ?>

                </div>


                <button class="btn btn-danger">
                    Guardar
                </button>

                <a
                    href="noticias.php"
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
                Noticias existentes
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
                        <th>Foto</th>
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

                            <?php if (!empty($r['foto'])): ?>

                                <img
                                    class="preview-admin"
                                    src="<?= ADMIN_URL ?>/uploads/noticias/<?= e($r['foto']) ?>"
                                    alt=""
                                >

                            <?php else: ?>

                                —

                            <?php endif; ?>

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
                                    onclick="return confirm('¿Eliminar esta noticia?')"
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
