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
            'Solo un Administrador puede eliminar boletines.';

        adminRedirect('boletines.php');
    }

    $id = (int)$_GET['eliminar'];

    $st = $pdo->prepare("
        SELECT
            foto_portada,
            archivo_pdf
        FROM boletines
        WHERE id = ?
    ");

    $st->execute([$id]);
    $old = $st->fetch();

    $pdo->prepare("
        DELETE FROM boletines
        WHERE id = ?
    ")->execute([$id]);

    if ($old) {
        eliminarArchivoSubido(
            'boletines',
            $old['foto_portada'] ?? null
        );

        eliminarArchivoSubido(
            'boletines',
            $old['archivo_pdf'] ?? null
        );
    }

    $_SESSION['alerta_tipo'] = 'success';
    $_SESSION['alerta_mensaje'] =
        'Boletín eliminado.';

    adminRedirect('boletines.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $fecha = $_POST['fecha_publicacion'] ?? date('Y-m-d');

    try {
            $old = [
                'foto_portada' => null,
                'archivo_pdf' => null
            ];

            if ($id) {
                $st = $pdo->prepare("
                    SELECT
                        foto_portada,
                        archivo_pdf
                    FROM boletines
                    WHERE id = ?
                ");

                $st->execute([$id]);

                $old =
                    $st->fetch()
                    ?: $old;
            }

            $foto = subirArchivo(
                'foto_portada',
                'boletines',
                ['jpg', 'jpeg', 'png', 'webp'],
                8 * 1024 * 1024
            );

            $pdf = subirArchivo(
                'archivo_pdf',
                'boletines',
                ['pdf'],
                10 * 1024 * 1024
            );

            $fotoFinal =
                $foto
                ?: $old['foto_portada'];

            $pdfFinal =
                $pdf
                ?: $old['archivo_pdf'];

            if ($id) {
                $pdo->prepare("
                    UPDATE boletines
                    SET
                        numero_boletin = '',
                        resumen = '',
                        foto_portada = ?,
                        archivo_pdf = ?,
                        fecha_publicacion = ?,
                        usuario_id = ?
                    WHERE id = ?
                ")->execute([
                    $fotoFinal,
                    $pdfFinal,
                    $fecha,
                    (int)$usuario['id'],
                    $id
                ]);
            } else {
                $pdo->prepare("
                    INSERT INTO boletines
                    (
                        numero_boletin,
                        resumen,
                        foto_portada,
                        archivo_pdf,
                        fecha_publicacion,
                        usuario_id
                    )
                    VALUES
                    ('', '', ?, ?, ?, ?)
                ")->execute([
                    $fotoFinal,
                    $pdfFinal,
                    $fecha,
                    (int)$usuario['id']
                ]);
            }

            if ($foto && $old['foto_portada']) {
                eliminarArchivoSubido(
                    'boletines',
                    $old['foto_portada']
                );
            }

            if ($pdf && $old['archivo_pdf']) {
                eliminarArchivoSubido(
                    'boletines',
                    $old['archivo_pdf']
                );
            }

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Boletín guardado correctamente.';

            adminRedirect('boletines.php');

        } catch (Throwable $e) {
            $error =
                'No se pudo guardar: ' .
                $e->getMessage();
        }
}

if (isset($_GET['editar'])) {
    $st = $pdo->prepare("
        SELECT *
        FROM boletines
        WHERE id = ?
    ");

    $st->execute([
        (int)$_GET['editar']
    ]);

    $editar = $st->fetch();
}

$filas = $pdo->query("
    SELECT *
    FROM boletines
    ORDER BY
        fecha_publicacion DESC,
        id DESC
")->fetchAll();

adminHeader('Boletines NTEP');
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
                    ? 'Editar boletín'
                    : 'Nuevo boletín'
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
                        Portada
                    </label>

                    <input
                        class="form-control"
                        type="file"
                        name="foto_portada"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <?php if (!empty($editar['foto_portada'])): ?>

                        <img
                            class="preview-admin mt-2"
                            src="<?= ADMIN_URL ?>/uploads/boletines/<?= e($editar['foto_portada']) ?>"
                            alt=""
                        >

                    <?php endif; ?>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        PDF
                    </label>

                    <input
                        class="form-control"
                        type="file"
                        name="archivo_pdf"
                        accept=".pdf"
                    >

                    <?php if (!empty($editar['archivo_pdf'])): ?>

                        <a
                            class="small d-inline-block mt-2"
                            target="_blank"
                            href="<?= ADMIN_URL ?>/uploads/boletines/<?= e($editar['archivo_pdf']) ?>"
                        >
                            Ver PDF actual
                        </a>

                    <?php endif; ?>

                </div>


                <button class="btn btn-danger">
                    Guardar
                </button>

                <a
                    href="boletines.php"
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
                Boletines existentes
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
                        <th>Portada</th>
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

                            <?php if (!empty($r['foto_portada'])): ?>

                                <img
                                    class="preview-admin"
                                    src="<?= ADMIN_URL ?>/uploads/boletines/<?= e($r['foto_portada']) ?>"
                                    alt=""
                                >

                            <?php else: ?>

                                —

                            <?php endif; ?>

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
                                    onclick="return confirm('¿Eliminar este boletín?')"
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
