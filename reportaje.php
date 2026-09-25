<?php

require_once __DIR__ . '/admin/config/conexion.php';

$pdo = db();

$id = (int)($_GET['id'] ?? 0);

$reportaje = null;
$fotos = [];

if ($id > 0) {

    $st = $pdo->prepare("
        SELECT
            r.id,
            r.titulo,
            r.resumen_corto,
            r.desarrollo,
            r.foto_principal,
            r.pdf_adjunto,
            r.fecha_publicacion,
            r.es_destacado,
            COALESCE(
                NULLIF(a.nickname, ''),
                NULLIF(CONCAT_WS(' ', a.nombres, a.ap_paterno), ''),
                'Redacción'
            ) AS autor
        FROM reportajes r
        LEFT JOIN autores a
            ON a.id = r.autor_id
        WHERE r.id = ?
        LIMIT 1
    ");

    $st->execute([$id]);
    $reportaje = $st->fetch(PDO::FETCH_ASSOC);

    if ($reportaje) {
        try {
            $stFotos = $pdo->prepare("
                SELECT
                    id,
                    url_foto,
                    orden,
                    descripcion
                FROM reportajes_fotos
                WHERE reportaje_id = ?
                ORDER BY orden ASC, id ASC
            ");

            $stFotos->execute([$id]);
            $fotos = $stFotos->fetchAll(PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            $fotos = [];
        }
    }
}

function rutaArchivoReportaje(?string $archivo): string
{
    $archivo = basename(trim((string)$archivo));

    if ($archivo === '') {
        return '';
    }

    return 'admin/uploads/reportajes/' . rawurlencode($archivo);
}

function fechaDetalle(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }

    $t = strtotime($fecha);

    if (!$t) {
        return $fecha;
    }

    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    return date('d', $t) . ' de ' . $meses[(int)date('n', $t)] . ' de ' . date('Y', $t);
}

function desarrolloSeguro(?string $html): string
{
    $html = (string)$html;

    return strip_tags(
        $html,
        '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><blockquote><a>'
    );
}

?>
<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
<!doctype html>
<html lang="es">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no"
    >

    <title>
        <?= $reportaje
            ? htmlspecialchars($reportaje['titulo'], ENT_QUOTES, 'UTF-8')
            : 'Reportaje'
        ?>
        - Diálogo y Desarrollo Perú
    </title>

    <link
        href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style-starter.css"
    >

    <style>
        .reportaje-detalle {
            max-width: 1000px;
        }

        .reportaje-detalle-imagen {
            width: 100%;
            max-height: 620px;
            object-fit: cover;
        }

        .reportaje-resumen {
            font-size: 20px;
            line-height: 1.65;
        }

        .reportaje-contenido {
            font-size: 17px;
            line-height: 1.9;
        }

        .reportaje-contenido p {
            margin-bottom: 18px;
        }

        .reportaje-galeria img {
            width: 100%;
            height: 260px;
            object-fit: cover;
        }
    </style>

</head>

<body>

<!-- header -->
<header id="site-header" class="fixed-top">

    <div class="container">

        <nav class="navbar navbar-expand-lg stroke">

            <a class="navbar-brand" href="index.php">
                <img
                    src="assets/images/logo.png"
                    onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo.png';"
                    alt="Diálogo y Desarrollo Perú"
                    style="height:75px;"
                >
            </a>

            <button
                class="navbar-toggler collapsed bg-gradient"
                type="button"
                data-toggle="collapse"
                data-target="#navbarTogglerDemo02"
                aria-controls="navbarTogglerDemo02"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
                <span class="navbar-toggler-icon fa icon-close fa-times"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarTogglerDemo02">

                <ul class="navbar-nav ml-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="index.php#actualidad">Actualidad</a>
                    </li>

                    <li class="nav-item active">
                        <a class="nav-link" href="reportajes.php">Reportajes</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="podcasts.php">Podcast</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="boletines.php">Boletín NTEP</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="alianzas.php">Alianzas</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="sobre-dd.php">Sobre D&D</a>
                    </li>

                    <li class="ml-2">
                        <a
                            href="contacto.php"
                            class="btn btn-style btn-outline-secondary"
                        >
                            Contacto
                        </a>
                    </li>

                </ul>

            </div>

        </nav>

    </div>

</header>
<!-- //header -->


<section class="breadcrumb-area py-sm-5 py-4">

    <div class="container">

        <div class="row">

            <div class="col-md-12">

                <div class="breadcrumb-contents">

                    <h2 class="title-big">Reportaje</h2>

                    <div class="breadcrumb">

                        <ul>

                            <li>
                                <a href="index.php">Inicio</a>
                            </li>

                            <li>
                                <a href="reportajes.php">Reportajes</a>
                            </li>

                        </ul>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<main class="py-5">

    <div class="container reportaje-detalle">

        <?php if (!$reportaje): ?>

            <div class="alert alert-warning">

                El reportaje solicitado no existe.

                <a href="reportajes.php">
                    Volver a Reportajes
                </a>

            </div>

        <?php else: ?>

            <article>

                <div class="mb-4">

                    <?php if (!empty($reportaje['es_destacado'])): ?>

                        <span class="badge badge-danger mb-3">
                            Destacado
                        </span>

                    <?php endif; ?>

                    <h1 class="title-big mb-3">
                        <?= htmlspecialchars(
                            $reportaje['titulo'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h1>

                    <p class="text-muted">

                        <?php if (!empty($reportaje['fecha_publicacion'])): ?>

                            <?= htmlspecialchars(
                                fechaDetalle($reportaje['fecha_publicacion']),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        <?php endif; ?>

                        <?php if (!empty($reportaje['autor'])): ?>

                            · Por
                            <?= htmlspecialchars(
                                $reportaje['autor'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        <?php endif; ?>

                    </p>

                </div>


                <?php if (!empty($reportaje['resumen_corto'])): ?>

                    <p class="reportaje-resumen mb-4">

                        <?= nl2br(
                            htmlspecialchars(
                                $reportaje['resumen_corto'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        ) ?>

                    </p>

                <?php endif; ?>


                <?php if (!empty($reportaje['foto_principal'])): ?>

                    <img
                        src="<?= htmlspecialchars(
                            rutaArchivoReportaje($reportaje['foto_principal']),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $reportaje['titulo'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        class="img-fluid radius-image reportaje-detalle-imagen mb-5"
                        onerror="this.style.display='none';"
                    >

                <?php endif; ?>


                <div class="reportaje-contenido">

                    <?= desarrolloSeguro(
                        $reportaje['desarrollo'] ?? ''
                    ) ?>

                </div>


                <?php if (!empty($reportaje['pdf_adjunto'])): ?>

                    <div class="mt-5">

                        <a
                            href="<?= htmlspecialchars(
                                rutaArchivoReportaje($reportaje['pdf_adjunto']),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            target="_blank"
                            class="btn btn-style btn-primary"
                        >
                            Ver PDF adjunto
                        </a>

                    </div>

                <?php endif; ?>


                <?php if ($fotos): ?>

                    <div class="mt-5">

                        <h3 class="title-big mb-4">
                            Galería
                        </h3>

                        <div class="row reportaje-galeria">

                            <?php foreach ($fotos as $foto): ?>

                                <?php if (!empty($foto['url_foto'])): ?>

                                    <div class="col-md-6 mb-4">

                                        <img
                                            src="<?= htmlspecialchars(
                                                rutaArchivoReportaje($foto['url_foto']),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt=""
                                            class="img-fluid radius-image"
                                        >

                                        <?php if (!empty($foto['descripcion'])): ?>

                                            <p class="mt-2 text-muted">

                                                <?= htmlspecialchars(
                                                    $foto['descripcion'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </p>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>


                <div class="mt-5">

                    <a
                        href="reportajes.php"
                        class="btn btn-style btn-outline-secondary"
                    >
                        Volver a Reportajes
                    </a>

                </div>

            </article>

        <?php endif; ?>

    </div>

</main>


<!-- footer block -->
<section class="w3l-footer-29-main py-5" id="footer">

    <div class="footer-29 py-md-3">

        <div class="container">

            <div class="row footer-top-29">

                <div class="col-lg-6 col-md-6 footer-list-29 footer-1">

                    <h6 class="footer-title-29">
                        Quiénes Somos
                    </h6>

                    <p>
                        Somos un espacio de periodismo independiente que busca
                        visibilizar las acciones de diálogo en el país desde una
                        mirada constructiva.
                    </p>

                </div>

                <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">

                    <ul>

                        <h6 class="footer-title-29">
                            Contenido
                        </h6>

                        <li>
                            <a href="index.php#actualidad">Noticias</a>
                        </li>

                        <li>
                            <a href="reportajes.php">Reportajes</a>
                        </li>

                        <li>
                            <a href="podcasts.php">Podcast</a>
                        </li>

                        <li>
                            <a href="boletines.php">Boletines</a>
                        </li>

                    </ul>

                </div>

                <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">

                    <div class="properties">

                        <h6 class="footer-title-29">
                            Contacto
                        </h6>

                        <ul>

                            <li>
                                <a href="#">
                                    info@dialogoydesarrollo.com.pe
                                </a>
                            </li>

                        </ul>

                    </div>

                </div>

            </div>

            <div class="bottom-copies text-center">

                <p class="copy-footer-29">
                    © 2026 Diálogo y Desarrollo Perú.
                    All rights reserved.
                </p>

            </div>

        </div>

    </div>

</section>
<!-- //footer block -->


<script src="assets/js/jquery-3.3.1.min.js"></script>

<script>
    $(window).on("scroll", function () {
        var scroll = $(window).scrollTop();

        if (scroll >= 80) {
            $("#site-header").addClass("nav-fixed");
        } else {
            $("#site-header").removeClass("nav-fixed");
        }
    });

    $(".navbar-toggler").on("click", function () {
        $("header").toggleClass("active");
    });
</script>

<script src="assets/js/bootstrap.min.js"></script>

</body>

</html>
