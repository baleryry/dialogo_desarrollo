<?php

require_once __DIR__ . '/admin/config/conexion.php';

$pdo = db();

/* =========================================================
   OBTENER PODCASTS
   ========================================================= */

$podcasts = $pdo->query("
    SELECT *
    FROM podcasts
    ORDER BY fecha_publicacion DESC, id DESC
")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   CONVERTIR URL A REPRODUCTOR
   ========================================================= */

function obtenerEmbed($url)
{
    $url = trim((string)$url);

    if ($url === '') {
        return '';
    }

    /* Ya es embed */
    if (strpos($url, '/embed/') !== false) {
        return $url;
    }

    /* YouTube normal */
    if (preg_match(
        '~youtube\.com/watch\?v=([^&]+)~',
        $url,
        $m
    )) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    /* YouTube corto */
    if (preg_match(
        '~youtu\.be/([^?&]+)~',
        $url,
        $m
    )) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    /* YouTube Shorts */
    if (preg_match(
        '~youtube\.com/shorts/([^?&]+)~',
        $url,
        $m
    )) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    /* Spotify */
    if (strpos(
        $url,
        'open.spotify.com/'
    ) !== false) {

        return str_replace(
            'open.spotify.com/',
            'open.spotify.com/embed/',
            $url
        );
    }

    /* SoundCloud */
    if (strpos(
        $url,
        'soundcloud.com/'
    ) !== false) {

        return
            'https://w.soundcloud.com/player/?url='
            . urlencode($url);
    }

    return $url;
}


/* =========================================================
   CONTAR PODCASTS PUBLICADOS
   ========================================================= */

$totalPodcasts = 0;

foreach ($podcasts as $p) {

    if (
        !isset($p['estado'])
        ||
        $p['estado'] === 'publicado'
    ) {
        $totalPodcasts++;
    }

}

?>
<!doctype html>

<html lang="es">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no"
    >

    <title>
        Podcast - Diálogo y Desarrollo Perú
    </title>


    <!-- GOOGLE FONTS -->

    <link
        href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&subset=latin-ext,vietnamese"
        rel="stylesheet"
    >


    <!-- CSS ORIGINAL -->

    <link
        rel="stylesheet"
        href="assets/css/style-starter.css"
    >
     <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    >


    <style>
        /* Solo presentación: la conexión y las consultas PHP no se modifican */
        .podcast-section {
            background: #f5f6f8;
        }

        .podcast-grid-title {
            margin-bottom: 30px;
        }

        .podcast-card {
            height: 100%;
            margin-bottom: 28px;
        }

        .podcast-cover {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 5px 18px rgba(0, 0, 0, .08);
            cursor: pointer;
        }

        .podcast-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .25s ease;
        }

        .podcast-cover:hover img {
            transform: scale(1.035);
        }

        .podcast-play {
            position: absolute;
            right: 13px;
            bottom: 13px;
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 50%;
            background: #e31b23;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 5px 16px rgba(0, 0, 0, .24);
            cursor: pointer;
        }

        .podcast-info {
            padding: 14px 3px 0;
        }

        .podcast-plataforma {
            display: inline-block;
            margin-bottom: 6px;
            font-size: 11px;
            line-height: 1;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .45px;
            color: #e31b23;
        }

        .podcast-titulo {
            font-size: 17px;
            line-height: 1.3;
            font-weight: 600;
            margin: 0 0 7px;
            color: #20242a;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 44px;
        }

        .podcast-fecha {
            font-size: 12px;
            color: #888;
            margin-bottom: 7px;
        }

        .podcast-descripcion {
            margin: 0;
            font-size: 13px;
            line-height: 1.45;
            color: #666;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .podcast-vacio {
            text-align: center;
            padding: 60px 20px;
        }

        .podcast-modal .modal-content {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 14px 45px rgba(0, 0, 0, .24);
        }

        .podcast-modal .modal-header {
            border-bottom: 1px solid #eee;
        }

        .podcast-modal iframe {
            width: 100%;
            display: block;
            border: 0;
        }

        @media (max-width: 767.98px) {
            .podcast-titulo {
                font-size: 16px;
                min-height: auto;
            }

            .podcast-play {
                width: 40px;
                height: 40px;
            }
        }
    </style>

</head>


<body>


<!-- =====================================================
     HEADER ORIGINAL
     ===================================================== -->

<header
    id="site-header"
    class="fixed-top"
>

    <div class="container">

        <nav
            class="navbar navbar-expand-lg stroke"
        >


            <!-- LOGO -->

            <a
                class="navbar-brand"
                href="index.php"
            >

                <img
                    src="assets/images/logo.png"
                    onerror="
                        this.onerror=null;
                        this.src=
                        'https://www.dialogoydesarrollo.com.pe/assets/images/logo.png';
                    "
                    alt="Diálogo y Desarrollo Perú"
                    style="height:75px;"
                >

            </a>


            <!-- BOTON MOVIL -->

            <button
                class="navbar-toggler collapsed bg-gradient"
                type="button"
                data-toggle="collapse"
                data-target="#navbarTogglerDemo02"
                aria-controls="navbarTogglerDemo02"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >

                <span
                    class="
                        navbar-toggler-icon
                        fa icon-expand fa-bars
                    "
                ></span>

                <span
                    class="
                        navbar-toggler-icon
                        fa icon-close fa-times
                    "
                ></span>

            </button>


            <!-- MENU -->

            <div
                class="collapse navbar-collapse"
                id="navbarTogglerDemo02"
            >

                <ul
                    class="navbar-nav ml-auto"
                >


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="index.php"
                        >
                            Inicio
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="index.php#actualidad"
                        >
                            Actualidad
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="reportajes.php"
                        >
                            Reportajes
                        </a>

                    </li>


                    <li class="nav-item active">

                        <a
                            class="nav-link"
                            href="podcasts.php"
                        >
                            Podcast
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="boletines.php"
                        >
                            Boletín NTEP
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="alianzas.php"
                        >
                            Alianzas
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="sobre-dd.php"
                        >
                            Sobre D&D
                        </a>

                    </li>


                    <li class="ml-2">

                        <a
                            href="contacto.php"
                            class="
                                btn
                                btn-style
                                btn-outline-secondary
                            "
                        >
                            Contacto
                        </a>

                    </li>


                </ul>

            </div>

        </nav>

    </div>

</header>


<!-- =====================================================
     TITULO
     ===================================================== -->

<section
    class="breadcrumb-area py-sm-5 py-4"
>

    <div class="container">

        <div class="row">

            <div class="col-md-12">

                <div
                    class="breadcrumb-contents"
                >

                    <h2 class="title-big">

                        Podcast

                    </h2>

                    <p class="mt-2">

                        Escucha nuestros últimos
                        contenidos y entrevistas.

                    </p>

                    <p>

                        <strong>
                            <?= $totalPodcasts ?>
                        </strong>

                        podcast disponibles

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     CONTENIDO PODCAST
     ===================================================== -->

<section class="w3l-homeblock3 py-5 podcast-section">
    <div class="container py-lg-5 py-md-4">

        <div class="text-center podcast-grid-title">
            <h5 class="title-small mb-1">Diálogo y Desarrollo Perú</h5>
            <h3 class="title-big mb-2">Nuestros Podcast</h3>
            <p class="mb-0"><?= (int)$totalPodcasts ?> podcast disponibles</p>
        </div>

        <div class="row">

            <?php $hayPublicados = false; ?>

            <?php foreach ($podcasts as $podcast): ?>

                <?php
                if (
                    isset($podcast['estado']) &&
                    $podcast['estado'] !== 'publicado'
                ) {
                    continue;
                }

                $hayPublicados = true;
                $embed = obtenerEmbed($podcast['url_embed'] ?? '');

                /* PORTADA */
                $portada = 'assets/images/podcast.png';

                if (!empty($podcast['portada'])) {
                    $nombrePortada = basename($podcast['portada']);
                    $rutaAdmin = __DIR__ . '/admin/uploads/podcasts/' . $nombrePortada;
                    $rutaPublica = __DIR__ . '/uploads/podcasts/' . $nombrePortada;

                    if (file_exists($rutaAdmin)) {
                        $portada = 'admin/uploads/podcasts/' . $nombrePortada;
                    } elseif (file_exists($rutaPublica)) {
                        $portada = 'uploads/podcasts/' . $nombrePortada;
                    }
                }

                /* FECHA */
                $fecha = '';
                if (!empty($podcast['fecha_publicacion'])) {
                    $timestamp = strtotime($podcast['fecha_publicacion']);
                    if ($timestamp !== false) {
                        $fecha = date('d/m/Y', $timestamp);
                    }
                }

                $idPodcast = (int)($podcast['id'] ?? 0);
                ?>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <article class="podcast-card">

                        <div
                            class="podcast-cover"
                            <?php if ($embed !== ''): ?>
                                data-toggle="modal"
                                data-target="#podcastModal<?= $idPodcast ?>"
                            <?php endif; ?>
                        >
                            <img
                                src="<?= htmlspecialchars($portada, ENT_QUOTES, 'UTF-8') ?>"
                                onerror="this.onerror=null;this.src='assets/images/podcast.png';"
                                alt="<?= htmlspecialchars($podcast['titulo'] ?? 'Podcast', ENT_QUOTES, 'UTF-8') ?>"
                            >

                            <?php if ($embed !== ''): ?>
                                <button
                                    type="button"
                                    class="podcast-play"
                                    aria-label="Reproducir <?= htmlspecialchars($podcast['titulo'] ?? 'podcast', ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <span class="fa fa-play"></span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="podcast-info">

                            <?php if (!empty($podcast['plataforma'])): ?>
                                <span class="podcast-plataforma">
                                    <?= htmlspecialchars($podcast['plataforma'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                            <h4 class="podcast-titulo">
                                <?= htmlspecialchars($podcast['titulo'] ?? 'Podcast', ENT_QUOTES, 'UTF-8') ?>
                            </h4>

                            <?php if ($fecha !== ''): ?>
                                <div class="podcast-fecha">
                                    <span class="fa fa-calendar-o"></span>
                                    <?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($podcast['descripcion'])): ?>
                                <p class="podcast-descripcion">
                                    <?= htmlspecialchars($podcast['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php endif; ?>

                        </div>
                    </article>
                </div>

                <?php if ($embed !== ''): ?>
                    <div
                        class="modal fade podcast-modal"
                        id="podcastModal<?= $idPodcast ?>"
                        tabindex="-1"
                        role="dialog"
                        aria-hidden="true"
                    >
                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <?= htmlspecialchars($podcast['titulo'] ?? 'Podcast', ENT_QUOTES, 'UTF-8') ?>
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body p-0">
                                    <iframe
                                        src="<?= htmlspecialchars($embed, ENT_QUOTES, 'UTF-8') ?>"
                                        height="420"
                                        allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                                        allowfullscreen
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>

            <?php if (!$hayPublicados): ?>
                <div class="col-12">
                    <div class="podcast-vacio">
                        <img
                            src="assets/images/podcast.png"
                            alt="Podcast"
                            style="max-width:120px; margin-bottom:18px;"
                        >
                        <h3>Próximamente</h3>
                        <p>Aún no tenemos podcasts publicados.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>


<!-- =====================================================
     REDES SOCIALES
     ===================================================== -->

<div class="middle py-5">

    <div
        class="
            container
            py-xl-5
            py-lg-3
        "
    >

        <div
            class="
                welcome-left
                text-center
                py-md-5
                py-3
            "
        >

            <h3 class="title-big">

                Síguenos en nuestras
                Redes Sociales

            </h3>


            <div
                class="
                    main-social-footer-29
                "
            >

                <a
                    target="_blank"
                    href="
                        https://www.facebook.com/DialogoyDesarrolloPeru
                    "
                    class="facebook"
                >

                    <span
                        class="
                            fa
                            fa-facebook-square
                            fa-2x
                        "
                    ></span>

                </a>


                <a
                    target="_blank"
                    href="
                        https://www.instagram.com/dialogo.y.desarrollo/
                    "
                    class="instagram"
                >

                    <span
                        class="
                            fa
                            fa-instagram
                            fa-2x
                        "
                    ></span>

                </a>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     FOOTER
     ===================================================== -->

<section
    class="
        w3l-footer-29-main
        py-5
    "
    id="footer"
>

    <div
        class="
            footer-29
            py-md-3
        "
    >

        <div class="container">

            <div
                class="
                    row
                    footer-top-29
                "
            >


                <div
                    class="
                        col-lg-6
                        col-md-6
                        footer-list-29
                        footer-1
                    "
                >

                    <h6
                        class="
                            footer-title-29
                        "
                    >

                        Quiénes Somos

                    </h6>


                    <p>

                        Somos un espacio
                        de periodismo
                        independiente que
                        busca visibilizar
                        las acciones de
                        diálogo en el país
                        desde una mirada
                        constructiva.

                    </p>

                </div>


                <div
                    class="
                        col-lg-3
                        col-md-6
                        footer-list-29
                        footer-2
                    "
                >

                    <h6
                        class="
                            footer-title-29
                        "
                    >

                        Contenido

                    </h6>


                    <ul>

                        <li>

                            <a
                                href="
                                    reportajes.php
                                "
                            >
                                Reportajes
                            </a>

                        </li>


                        <li>

                            <a
                                href="
                                    podcasts.php
                                "
                            >
                                Podcast
                            </a>

                        </li>


                        <li>

                            <a
                                href="
                                    boletines.php
                                "
                            >
                                Boletines
                            </a>

                        </li>

                    </ul>

                </div>


                <div
                    class="
                        col-lg-3
                        col-md-6
                        footer-list-29
                        footer-3
                    "
                >

                    <h6
                        class="
                            footer-title-29
                        "
                    >

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


            <div
                class="
                    bottom-copies
                    text-center
                "
            >

                <p
                    class="
                        copy-footer-29
                    "
                >

                    © 2026
                    Diálogo y Desarrollo Perú.

                    All rights reserved.

                </p>

            </div>

        </div>

    </div>

</section>


<!-- JAVASCRIPT -->

<script
    src="
        assets/js/jquery-3.3.1.min.js
    "
></script>

<script
    src="
        assets/js/bootstrap.min.js
    "
></script>


</body>

</html>