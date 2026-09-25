<?php

require_once __DIR__ . '/includes/admin_layout.php';

exigirLogin();

$pdo = db();
$usuario = usuarioActual();
$error = '';
$editar = null;

if (isset($_GET['eliminar_foto'])) {
    $fotoId = (int)$_GET['eliminar_foto'];

    $st = $pdo->prepare("
        SELECT id, reportaje_id, url_foto
        FROM reportajes_fotos
        WHERE id = ?
    ");

    $st->execute([$fotoId]);
    $foto = $st->fetch();

    if ($foto) {
        $pdo->prepare("
            DELETE FROM reportajes_fotos
            WHERE id = ?
        ")->execute([$fotoId]);

        eliminarArchivoSubido(
            'reportajes',
            $foto['url_foto']
        );

        $_SESSION['alerta_tipo'] = 'success';
        $_SESSION['alerta_mensaje'] =
            'Foto adicional eliminada.';

        adminRedirect(
            'reportajes.php?editar=' .
            (int)$foto['reportaje_id']
        );
    }
}

if (isset($_GET['eliminar'])) {
    if (!esAdministrador($usuario['rol'] ?? '')) {
        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'Solo un Administrador puede eliminar reportajes.';

        adminRedirect('reportajes.php');
    }

    $id = (int)$_GET['eliminar'];

    $st = $pdo->prepare("
        SELECT foto_principal, pdf_adjunto
        FROM reportajes
        WHERE id = ?
    ");

    $st->execute([$id]);
    $old = $st->fetch();

    $stFotos = $pdo->prepare("
        SELECT url_foto
        FROM reportajes_fotos
        WHERE reportaje_id = ?
    ");

    $stFotos->execute([$id]);
    $fotos = $stFotos->fetchAll();

    $pdo->beginTransaction();

    try {
        $pdo->prepare("
            DELETE FROM reportajes_fotos
            WHERE reportaje_id = ?
        ")->execute([$id]);

        $pdo->prepare("
            DELETE FROM reportajes
            WHERE id = ?
        ")->execute([$id]);

        $pdo->commit();

        if ($old) {
            eliminarArchivoSubido(
                'reportajes',
                $old['foto_principal'] ?? null
            );

            eliminarArchivoSubido(
                'reportajes',
                $old['pdf_adjunto'] ?? null
            );
        }

        foreach ($fotos as $f) {
            eliminarArchivoSubido(
                'reportajes',
                $f['url_foto'] ?? null
            );
        }

        $_SESSION['alerta_tipo'] = 'success';
        $_SESSION['alerta_mensaje'] =
            'Reportaje eliminado.';

    } catch (Throwable $e) {
        $pdo->rollBack();

        $_SESSION['alerta_tipo'] = 'danger';
        $_SESSION['alerta_mensaje'] =
            'No se pudo eliminar: ' .
            $e->getMessage();
    }

    adminRedirect('reportajes.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $resumen = trim($_POST['resumen_corto'] ?? '');
    $desarrollo = trim($_POST['desarrollo'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? date('Y-m-d');
    $destacado = isset($_POST['es_destacado']) ? 1 : 0;
    $autorId = (int)($_POST['autor_id'] ?? 0);
    $autorId = $autorId > 0 ? $autorId : null;

    if ($titulo === '') {
        $error = 'El título es obligatorio.';
    } else {
        try {
            $old = [
                'foto_principal' => null,
                'pdf_adjunto' => null
            ];

            if ($id) {
                $st = $pdo->prepare("
                    SELECT foto_principal, pdf_adjunto
                    FROM reportajes
                    WHERE id = ?
                ");

                $st->execute([$id]);

                $old =
                    $st->fetch()
                    ?: $old;
            }

            $foto = subirArchivo(
                'foto_principal',
                'reportajes',
                ['jpg', 'jpeg', 'png', 'webp'],
                8 * 1024 * 1024
            );

            $pdf = subirArchivo(
                'pdf_adjunto',
                'reportajes',
                ['pdf'],
                10 * 1024 * 1024
            );

            $fotoFinal =
                $foto
                ?: $old['foto_principal'];

            $pdfFinal =
                $pdf
                ?: $old['pdf_adjunto'];

            if ($id) {
                $pdo->prepare("
                    UPDATE reportajes
                    SET
                        titulo = ?,
                        resumen_corto = ?,
                        desarrollo = ?,
                        foto_principal = ?,
                        pdf_adjunto = ?,
                        fecha_publicacion = ?,
                        es_destacado = ?,
                        autor_id = ?,
                        usuario_id = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ")->execute([
                    $titulo,
                    $resumen,
                    $desarrollo,
                    $fotoFinal,
                    $pdfFinal,
                    $fecha,
                    $destacado,
                    $autorId,
                    (int)$usuario['id'],
                    $id
                ]);

                $reportajeId = $id;

            } else {
                $pdo->prepare("
                    INSERT INTO reportajes
                    (
                        titulo,
                        resumen_corto,
                        desarrollo,
                        foto_principal,
                        pdf_adjunto,
                        fecha_publicacion,
                        es_destacado,
                        autor_id,
                        usuario_id
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $titulo,
                    $resumen,
                    $desarrollo,
                    $fotoFinal,
                    $pdfFinal,
                    $fecha,
                    $destacado,
                    $autorId,
                    (int)$usuario['id']
                ]);

                $reportajeId =
                    (int)$pdo->lastInsertId();
            }

            $nuevasFotos =
                subirImagenesMultiples(
                    'fotos_adicionales',
                    'reportajes',
                    8 * 1024 * 1024
                );

            if ($nuevasFotos) {
                $stOrden = $pdo->prepare("
                    SELECT
                        COALESCE(MAX(orden), 0)
                    FROM reportajes_fotos
                    WHERE reportaje_id = ?
                ");

                $stOrden->execute([$reportajeId]);

                $orden =
                    (int)$stOrden->fetchColumn();

                $stInsert = $pdo->prepare("
                    INSERT INTO reportajes_fotos
                    (
                        reportaje_id,
                        url_foto,
                        orden,
                        descripcion
                    )
                    VALUES
                    (?, ?, ?, ?)
                ");

                foreach ($nuevasFotos as $nombreFoto) {
                    $orden++;

                    $stInsert->execute([
                        $reportajeId,
                        $nombreFoto,
                        $orden,
                        null
                    ]);
                }
            }

            if ($foto && $old['foto_principal']) {
                eliminarArchivoSubido(
                    'reportajes',
                    $old['foto_principal']
                );
            }

            if ($pdf && $old['pdf_adjunto']) {
                eliminarArchivoSubido(
                    'reportajes',
                    $old['pdf_adjunto']
                );
            }

            $_SESSION['alerta_tipo'] = 'success';
            $_SESSION['alerta_mensaje'] =
                'Reportaje guardado correctamente.';

            adminRedirect('reportajes.php');

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
        FROM reportajes
        WHERE id = ?
    ");

    $st->execute([
        (int)$_GET['editar']
    ]);

    $editar = $st->fetch();

    if (!$editar) {
        $_SESSION['alerta_tipo'] = 'warning';
        $_SESSION['alerta_mensaje'] =
            'El reportaje no existe.';

        adminRedirect('reportajes.php');
    }
}

$autores = $pdo->query("
    SELECT
        id,
        nombres,
        ap_paterno,
        ap_materno,
        nickname,
        es_nickname
    FROM autores
    ORDER BY nombres, ap_paterno
")->fetchAll();

$filas = $pdo->query("
    SELECT
        r.*,
        COALESCE(
            NULLIF(a.nickname, ''),
            NULLIF(
                CONCAT_WS(
                    ' ',
                    a.nombres,
                    a.ap_paterno
                ),
                ''
            ),
            'Redacción'
        ) AS autor
    FROM reportajes r
    LEFT JOIN autores a
        ON a.id = r.autor_id
    ORDER BY
        r.fecha_publicacion DESC,
        r.id DESC
")->fetchAll();

$fotosEditar = [];

if ($editar) {
    $st = $pdo->prepare("
        SELECT *
        FROM reportajes_fotos
        WHERE reportaje_id = ?
        ORDER BY orden, id
    ");

    $st->execute([
        (int)$editar['id']
    ]);

    $fotosEditar =
        $st->fetchAll();
}

adminHeader('Reportajes');
?>

<!-- Editor visual para el campo Desarrollo -->
<link
    href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
    rel="stylesheet"
>

<style>
    #editorDesarrollo {
        min-height: 260px;
        background: #fff;
    }

    .ql-toolbar.ql-snow {
        border-radius: 6px 6px 0 0;
    }

    .ql-container.ql-snow {
        border-radius: 0 0 6px 6px;
        font-size: 16px;
    }
</style>

<?php if ($error): ?>

<div class="alert alert-danger">
    <?= e($error) ?>
</div>

<?php endif; ?>


<div class="row g-4">

<div class="col-xl-5">

    <div class="card shadow-sm">

        <div class="card-header">

            <strong>
                <?= $editar
                    ? 'Editar reportaje'
                    : 'Nuevo reportaje'
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
                        Resumen corto
                    </label>

                    <textarea
                        class="form-control"
                        name="resumen_corto"
                        rows="3"
                    ><?= e($editar['resumen_corto'] ?? '') ?></textarea>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Desarrollo
                    </label>

                    <div class="form-text mb-2">
                        Puedes aplicar negrita, cursiva, subrayado, color,
                        títulos, listas, alineación y enlaces.
                    </div>

                    <textarea
                        name="desarrollo"
                        id="desarrollo"
                        class="d-none"
                    ><?= e($editar['desarrollo'] ?? '') ?></textarea>

                    <div id="editorDesarrollo"></div>

                </div>


                <div class="row">

                    <div class="col-md-6 mb-3">

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


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Autor
                        </label>

                        <select
                            class="form-select"
                            name="autor_id"
                        >

                            <option value="">
                                Redacción
                            </option>

                            <?php foreach ($autores as $a): ?>

                                <?php
                                $nombreAutor =
                                    !empty($a['nickname'])
                                    ? $a['nickname']
                                    : trim(
                                        $a['nombres'] .
                                        ' ' .
                                        $a['ap_paterno']
                                    );
                                ?>

                                <option
                                    value="<?= (int)$a['id'] ?>"
                                    <?= (int)(
                                        $editar['autor_id']
                                        ?? 0
                                    ) === (int)$a['id']
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    <?= e($nombreAutor) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>


                <div class="form-check mb-3">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="es_destacado"
                        id="es_destacado"
                        <?= !empty(
                            $editar['es_destacado']
                        )
                            ? 'checked'
                            : ''
                        ?>
                    >

                    <label
                        class="form-check-label"
                        for="es_destacado"
                    >
                        Reportaje destacado
                    </label>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Foto principal
                    </label>

                    <input
                        class="form-control"
                        type="file"
                        name="foto_principal"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <?php if (!empty($editar['foto_principal'])): ?>

                        <div class="mt-2">

                            <img
                                src="<?= ADMIN_URL ?>/uploads/reportajes/<?= e($editar['foto_principal']) ?>"
                                alt=""
                                class="preview-admin"
                            >

                        </div>

                    <?php endif; ?>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        PDF adjunto
                    </label>

                    <input
                        class="form-control"
                        type="file"
                        name="pdf_adjunto"
                        accept=".pdf"
                    >

                    <?php if (!empty($editar['pdf_adjunto'])): ?>

                        <a
                            class="small d-inline-block mt-2"
                            target="_blank"
                            href="<?= ADMIN_URL ?>/uploads/reportajes/<?= e($editar['pdf_adjunto']) ?>"
                        >
                            Ver PDF actual
                        </a>

                    <?php endif; ?>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Fotos adicionales
                    </label>

                    <input
                        class="form-control"
                        type="file"
                        name="fotos_adicionales[]"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                    >

                </div>


                <?php if ($fotosEditar): ?>

                    <div class="mb-3">

                        <label class="form-label">
                            Galería actual
                        </label>

                        <div
                            class="
                                d-flex
                                flex-wrap
                                gap-2
                            "
                        >

                            <?php foreach ($fotosEditar as $f): ?>

                                <div class="text-center">

                                    <img
                                        src="<?= ADMIN_URL ?>/uploads/reportajes/<?= e($f['url_foto']) ?>"
                                        alt=""
                                        class="preview-admin d-block mb-1"
                                    >

                                    <a
                                        class="
                                            btn
                                            btn-sm
                                            btn-outline-danger
                                        "
                                        href="?eliminar_foto=<?= (int)$f['id'] ?>"
                                        onclick="return confirm('¿Eliminar esta foto?')"
                                    >
                                        Quitar
                                    </a>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>


                <button class="btn btn-danger">
                    Guardar
                </button>

                <a
                    href="reportajes.php"
                    class="btn btn-secondary"
                >
                    Limpiar
                </a>

            </form>

        </div>

    </div>

</div>


<div class="col-xl-7">

    <div class="card shadow-sm">

        <div class="card-header">

            <strong>
                Reportajes existentes
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
                        <th>Autor</th>
                        <th>Fecha</th>
                        <th>Dest.</th>
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
                            <?= e($r['autor']) ?>
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

                        <td>
                            <?= !empty($r['es_destacado'])
                                ? '<i class="bi bi-star-fill text-warning"></i>'
                                : '—'
                            ?>
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
                                    onclick="return confirm('¿Eliminar este reportaje?')"
                                >
                                    Eliminar
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$filas): ?>

                    <tr>

                        <td
                            colspan="6"
                            class="
                                text-center
                                text-secondary
                                py-4
                            "
                        >
                            No hay reportajes.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

<!-- Quill: editor enriquecido para Desarrollo -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const textarea = document.getElementById('desarrollo');
    const editor = document.getElementById('editorDesarrollo');

    if (!textarea || !editor) {
        return;
    }

    const quill = new Quill('#editorDesarrollo', {
        theme: 'snow',
        placeholder: 'Escribe aquí el desarrollo del reportaje...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['blockquote', 'link'],
                ['clean']
            ]
        }
    });

    if (textarea.value.trim() !== '') {
        quill.root.innerHTML = textarea.value;
    }

    const form = textarea.closest('form');

    if (form) {
        form.addEventListener('submit', function () {
            const contenidoPlano = quill.getText().trim();

            if (contenidoPlano === '') {
                textarea.value = '';
            } else {
                textarea.value = quill.root.innerHTML;
            }
        });
    }
});
</script>

<?php adminFooter(); ?>
