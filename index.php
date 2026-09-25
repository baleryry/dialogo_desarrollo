<?php

require_once __DIR__ . '/admin/config/conexion.php';

$pdo = db();

$totalReportajes = $pdo
    ->query("SELECT COUNT(*) FROM reportajes")
    ->fetchColumn();

$reportajesInicio = $pdo->query("
    SELECT
        r.id,
        r.titulo,
        r.resumen_corto,
        r.fecha_publicacion,
        r.foto_principal,
        COALESCE(
            NULLIF(a.nickname, ''),
            NULLIF(CONCAT_WS(' ', a.nombres, a.ap_paterno), ''),
            'Redacción'
        ) AS autor
    FROM reportajes r
    LEFT JOIN autores a
        ON a.id = r.autor_id
    ORDER BY
        r.fecha_publicacion DESC,
        r.id DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   REPORTAJE DESTACADO
   Solo se usa para la sección principal.
   Si no hay ninguno marcado, se conserva el contenido original.
   ========================================================= */

$reportajeDestacado = $pdo->query("
    SELECT
        r.id,
        r.titulo,
        r.resumen_corto,
        r.fecha_publicacion,
        r.foto_principal,
        COALESCE(
            NULLIF(a.nickname, ''),
            NULLIF(CONCAT_WS(' ', a.nombres, a.ap_paterno), ''),
            'Redacción'
        ) AS autor
    FROM reportajes r
    LEFT JOIN autores a
        ON a.id = r.autor_id
    WHERE r.es_destacado = 1
    ORDER BY
        r.fecha_publicacion DESC,
        r.id DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);


/* =========================================================
   VIDEOS PARA LA SECCIÓN "ESPECIALES"
   Solo se agregan aquí; el resto del index permanece igual.
   ========================================================= */

$videosInicio = $pdo->query("
    SELECT
        id,
        titulo,
        url_embed,
        fecha_publicacion
    FROM videos
    ORDER BY
        fecha_publicacion DESC,
        id DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   PODCASTS PARA LA SECCIÓN "PODCAST" DEL INICIO
   Se agrega sin modificar las demás secciones.
   ========================================================= */

$columnasPodcastsInicio = $pdo
    ->query("SHOW COLUMNS FROM podcasts")
    ->fetchAll(PDO::FETCH_COLUMN);

$wherePodcastsInicio =
    in_array('estado', $columnasPodcastsInicio, true)
        ? "WHERE estado = 'publicado'"
        : "";

$podcastsInicio = $pdo->query("
    SELECT *
    FROM podcasts
    $wherePodcastsInicio
    ORDER BY
        fecha_publicacion DESC,
        id DESC
    LIMIT 4
")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   NOTICIAS PARA "NOTICIAS RECIENTES" DEL INICIO
   Se agregan antes de las noticias originales.
   ========================================================= */

$noticiasInicio = $pdo->query("
    SELECT
        id,
        titulo,
        foto,
        link_externo,
        fecha_publicacion
    FROM noticias
    ORDER BY
        fecha_publicacion DESC,
        id DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);




function videoEmbedInicio(?string $url): string
{
    $url = trim((string)$url);

    if ($url === '') {
        return '';
    }

    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }

    if (preg_match(
        '~youtube\.com/watch\?v=([^&]+)~',
        $url,
        $m
    )) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    if (preg_match(
        '~youtu\.be/([^?&]+)~',
        $url,
        $m
    )) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    if (preg_match(
        '~youtube\.com/shorts/([^?&]+)~',
        $url,
        $m
    )) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    return $url;
}

?>
<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>DDP Noticias - Diálogo y Desarrollo Perú</title>

    <!-- Google fonts -->
    
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="assets/css/style-starter.css">

    <!-- Font Awesome: necesario para los iconos fa-arrow-right, fa-download, redes sociales, etc. -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    >

    <!-- Ajuste visual: todas las portadas de Podcast del inicio al mismo tamaño -->
    <style>
        .area-box img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Solo noticias creadas desde el panel */
        .noticia-admin-img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            object-position: center;
            display: block;
        }
    </style>
  </head>
  <body>

<!-- header -->
<header id="site-header" class="fixed-top">
  <div class="container">
      <nav class="navbar navbar-expand-lg stroke">
          <!--<a class="navbar-brand" href="index.php">
              <span class="fa fa-video-camera"></span> V-Conference
          </a>
           if logo is image enable this   -->
      <a class="navbar-brand" href="index.php">
          <img src="assets/images/logo.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo.png';" alt="Your logo" title="Your logo" style="height:75px;" />
      </a> 

          <button class="navbar-toggler  collapsed bg-gradient" type="button" data-toggle="collapse"
              data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
              aria-label="Toggle navigation">
              <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
              <span class="navbar-toggler-icon fa icon-close fa-times"></span>
              </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
              <ul class="navbar-nav ml-auto">
                  <li class="nav-item active">
                      <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="index.php#actualidad">Actualidad</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="reportajes.php">Reportajes</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="podcasts.php">Podcast</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="boletines.php">Boletín NTEP</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="alianzas.php">Alianzas</a>
                  </li>
                  <li class="nav-item @@contact__active">
                      <a class="nav-link" href="sobre-dd.php">Sobre D&D</a>
                  </li>               
                  <li class="ml-2">
                      <a href="contacto.php" class="btn btn-style btn-outline-secondary">Contacto</a>
                  </li>
              </ul>
          </div>
          <!-- toggle switch for light and dark theme --
          <div class="mobile-position">
              <nav class="navigation">
                  <div class="theme-switch-wrapper">
                      <label class="theme-switch" for="checkbox">
                          <input type="checkbox" id="checkbox">
                          <div class="mode-container">
                              <i class="gg-sun"></i>
                              <i class="gg-moon"></i>
                          </div>
                      </label>
                  </div>
              </nav>
          </div>
          <!-- //toggle switch for light and dark theme -->
      </nav>
  </div>
</header>
<!-- //header -->
<section class="breadcrumb-area py-sm-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big">Reportajes</h2>
                    <!--<div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.php">Home</a>
                            </li>
                            <li class="active">
                                 Blog posts
                            </li>
                        </ul>
                    </div>-->
                </div>
            </div><!-- end .col-md-12 -->
        </div><!-- end .row -->
    </div><!-- end .container -->
</section>
<?php if ($reportajeDestacado): ?>

    <?php
        $fotoDestacada = trim((string)($reportajeDestacado['foto_principal'] ?? ''));

        if ($fotoDestacada !== '') {
            $rutaFotoAdmin = __DIR__ . '/admin/uploads/reportajes/' . basename($fotoDestacada);
            $rutaFotoPublica = __DIR__ . '/uploads/reportajes/' . basename($fotoDestacada);

            if (is_file($rutaFotoAdmin)) {
                $imagenDestacada = 'admin/uploads/reportajes/' . rawurlencode(basename($fotoDestacada));
            } elseif (is_file($rutaFotoPublica)) {
                $imagenDestacada = 'uploads/reportajes/' . rawurlencode(basename($fotoDestacada));
            } else {
                $imagenDestacada = 'assets/images/video.jpg';
            }
        } else {
            $imagenDestacada = 'assets/images/video.jpg';
        }

        $fechaDestacada = '';

        if (!empty($reportajeDestacado['fecha_publicacion'])) {
            $timestampDestacado = strtotime($reportajeDestacado['fecha_publicacion']);

            if ($timestampDestacado !== false) {
                $mesesDestacado = [
                    1 => 'Ene',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Abr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Ago',
                    9 => 'Set',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dic'
                ];

                $fechaDestacada =
                    $mesesDestacado[(int)date('n', $timestampDestacado)] .
                    ' ' .
                    date('d, Y', $timestampDestacado);
            }
        }

        $urlDestacado = 'reportaje.php?id=' . (int)$reportajeDestacado['id'];
    ?>

    <section class="w3l-video w3l-homeblock3 " id="video">
        <!-- /video-6-->
        <div class="container-fluid">
            <div class="video-grids-info row">

                <div class="video-gd-right col-lg-6 p-0">
                    <div class="position-relative">
                        <a href="<?= htmlspecialchars($urlDestacado, ENT_QUOTES, 'UTF-8') ?>">
                            <img
                                src="<?= htmlspecialchars($imagenDestacada, ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars((string)$reportajeDestacado['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                class="img-fluid"
                            >
                        </a>

                        <a href="#small-dialog" class="popup-with-zoom-anim play-view text-center position-absolute">
                        </a>

                        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
                            <iframe src="#" allow="autoplay; fullscreen" allowfullscreen=""></iframe>
                        </div>
                    </div>
                </div>

                <div class="video-gd-left col-lg-6 p-lg-5 p-4 align-self">
                    <div class="p-xl-4 p-0 video-wrap">

                        <?php if ($fechaDestacada !== ''): ?>
                            <h5>
                                <?= htmlspecialchars($fechaDestacada, ENT_QUOTES, 'UTF-8') ?>
                            </h5>
                        <?php endif; ?>

                        <h3 class="title-big text-left mb-4">
                            <a href="<?= htmlspecialchars($urlDestacado, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string)$reportajeDestacado['titulo'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h3>

                        <?php if (!empty($reportajeDestacado['resumen_corto'])): ?>
                            <p>
                                <?= nl2br(
                                    htmlspecialchars(
                                        (string)$reportajeDestacado['resumen_corto'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                ) ?>
                            </p>
                        <?php endif; ?>

                        <a
                            href="<?= htmlspecialchars($urlDestacado, ENT_QUOTES, 'UTF-8') ?>"
                            class="btn mt-4 p-0"
                        >
                            Leer
                            <span class="fa fa-arrow-right"></span>
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </section>

<?php else: ?>

<section class="w3l-video w3l-homeblock3 " id="video">
    <!-- /video-6-->
    <div class="container-fluid">
        <div class="video-grids-info row">
            <div class="video-gd-right col-lg-6 p-0">
                <div class="position-relative">
                    <a href="https://www.dialogoydesarrollo.com.pe/universidades-publicas-administran-casi-900-millones-de-canon-regalias-y-otros-recursos-determinados.html"><img src="assets/images/video.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/video.jpg';" alt="" class="img-fluid"></a>
                    <a href="#small-dialog" class="popup-with-zoom-anim play-view text-center position-absolute">
                        <!--<span class="video-play-icon">
                            <span class="fa fa-play"></span>
                        </span>-->
                    </a>
                    <!-- dialog itself, mfp-hide class is required to make dialog hidden -->
                    <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
                        <iframe src="#" allow="autoplay; fullscreen"allowfullscreen=""></iframe>
                    </div>
                </div>
            </div>
            <div class="video-gd-left col-lg-6 p-lg-5 p-4 align-self">
                <div class="p-xl-4 p-0 video-wrap">
                    <h5>Set 09, 2026</h5>
                    <h3 class="title-big text-left mb-4"><a href="https://www.dialogoydesarrollo.com.pe/universidades-publicas-administran-casi-900-millones-de-canon-regalias-y-otros-recursos-determinados.html">Universidades públicas administran casi S/900 millones de canon, regalías y otros recursos determinados</a></h3>
                    <p>Las universidades estatales concentran recursos provenientes de actividades extractivas. Pero han invertido la mitad. Especialistas dicen que una evaluación completa debería ir más allá del porcentaje ejecutado y preguntarse si esas inversiones producen mejores condiciones en formación e investigación...</p>
                    <a href="https://www.dialogoydesarrollo.com.pe/universidades-publicas-administran-casi-900-millones-de-canon-regalias-y-otros-recursos-determinados.html" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    <!--<a href="#start" class="btn btn-style btn-primary mt-md-5 mt-4"> Más Videos </a>-->
                </div>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<div class="grids-block-5 py-1">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">

            <div class="mb-3">
            </div>

            <div class="row">

                <?php foreach ($reportajesInicio as $r): ?>

                    <?php
                        $foto = trim((string)($r['foto_principal'] ?? ''));

                        if ($foto !== '') {
                            $rutaAdmin = __DIR__ . '/admin/uploads/reportajes/' . basename($foto);
                            $rutaPublica = __DIR__ . '/uploads/reportajes/' . basename($foto);

                            if (is_file($rutaAdmin)) {
                                $imagen = 'admin/uploads/reportajes/' . rawurlencode(basename($foto));
                            } elseif (is_file($rutaPublica)) {
                                $imagen = 'uploads/reportajes/' . rawurlencode(basename($foto));
                            } else {
                                $imagen = 'assets/images/video.jpg';
                            }
                        } else {
                            $imagen = 'assets/images/video.jpg';
                        }

                        $fecha = '';
                        if (!empty($r['fecha_publicacion'])) {
                            $timestamp = strtotime($r['fecha_publicacion']);
                            if ($timestamp !== false) {
                                $fecha = date('d/m/Y', $timestamp);
                            }
                        }

                        $urlDetalle = 'reportaje.php?id=' . (int)$r['id'];
                    ?>

                    <div class="col-lg-4 col-md-6 grids5-info mt-5">

                        <a href="<?= htmlspecialchars($urlDetalle, ENT_QUOTES, 'UTF-8') ?>" class="d-block">
                            <img
                                src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>"
                                onerror="this.onerror=null;this.src='assets/images/video.jpg';"
                                alt="<?= htmlspecialchars((string)$r['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                class="img-fluid"
                            />
                        </a>

                        <div class="blog-info">

                            <h5>
                                <?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?>
                            </h5>

                            <h4>
                                <a
                                    href="<?= htmlspecialchars($urlDetalle, ENT_QUOTES, 'UTF-8') ?>"
                                    class="d-block"
                                >
                                    <?= htmlspecialchars((string)$r['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h4>

                            <?php if (!empty($r['resumen_corto'])): ?>
                                <p>
                                    <?= htmlspecialchars((string)$r['resumen_corto'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php endif; ?>

                            <p style="font-size:14px;">
                                Por <?= htmlspecialchars((string)$r['autor'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <a
                                href="<?= htmlspecialchars($urlDetalle, ENT_QUOTES, 'UTF-8') ?>"
                                class="btn mt-4 p-0"
                            >
                                Leer
                                <span class="fa fa-arrow-right"></span>
                            </a>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

            <div class="pagination">
                <ul>
                    <li><a href="reportajes.php">Ver todos</a></li>
                </ul>
            </div>

        </div>
    </section>
</div>
<!--<section class="w3l-homeblock5 py-0">
    <div class="container py-lg-5 py-4">
        <div class="row">
            <div class="col-lg-8 align-self">
                <h5 class="title-small mb-2">DDP Noticias</h5>
                    <h3 class="title-banner">La minería ilegal no genera desarrollo para los territorios donde opera</h3>
                    <p class="mt-4">Informe del instituto VIDENZA analiza y compara el Índice de Desarrollo Humano en distritos del país, con presencia de minería informal e ilegal y las localidades sin presencia de actividad minera y los que tienen presencia de minería formal...</p>
                        <a href="mapa.html" class="btn btn-style btn-primary mt-md-5 mt-4">Ver Mapa</a>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4">
                <img src="assets/images/mapa-interactivo.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/mapa-interactivo.png';" class="img-fluid radius-image" alt="">
            </div>
        </div>
    </div>
</section>-->
<section class="breadcrumb-area py-sm-5 py-1">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big">Noticias Recientes</h2><a class="anchor" id="actualidad"></a>
                    <!--<div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.php">Home</a>
                            </li>
                            <li class="active">
                                 Blog posts
                            </li>
                        </ul>
                    </div>-->
                </div>
            </div><!-- end .col-md-12 -->
        </div><!-- end .row -->
    </div><!-- end .container -->
</section>
<div class="grids-block-5 py-5">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">

                <?php foreach ($noticiasInicio as $i => $noticia): ?>

                    <?php
                        $fotoNoticia = trim((string)($noticia['foto'] ?? ''));
                        $imagenNoticia = 'assets/images/video.jpg';

                        if ($fotoNoticia !== '') {
                            $nombreFotoNoticia = basename($fotoNoticia);

                            $rutaFotoNoticiaAdmin =
                                __DIR__
                                . '/admin/uploads/noticias/'
                                . $nombreFotoNoticia;

                            $rutaFotoNoticiaPublica =
                                __DIR__
                                . '/uploads/noticias/'
                                . $nombreFotoNoticia;

                            if (is_file($rutaFotoNoticiaAdmin)) {
                                $imagenNoticia =
                                    'admin/uploads/noticias/'
                                    . rawurlencode($nombreFotoNoticia);
                            } elseif (is_file($rutaFotoNoticiaPublica)) {
                                $imagenNoticia =
                                    'uploads/noticias/'
                                    . rawurlencode($nombreFotoNoticia);
                            }
                        }

                        $urlNoticia = trim(
                            (string)($noticia['link_externo'] ?? '')
                        );

                        if ($urlNoticia === '') {
                            $urlNoticia = '#';
                        }

                        $fechaNoticia = '';

                        if (!empty($noticia['fecha_publicacion'])) {
                            $timestampNoticia =
                                strtotime($noticia['fecha_publicacion']);

                            if ($timestampNoticia !== false) {
                                $fechaNoticia =
                                    date('d/m/Y', $timestampNoticia);
                            }
                        }

                        if ($i === 0) {
                            $claseNoticia = '';
                        } elseif ($i === 1) {
                            $claseNoticia = 'mt-lg-0 mt-5';
                        } else {
                            $claseNoticia = 'mt-md-0 mt-5';
                        }
                    ?>

                    <div class="col-lg-4 col-md-6 grids5-info <?= $claseNoticia ?>">

                        <a
                            target="_blank"
                            href="<?= htmlspecialchars(
                                $urlNoticia,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="d-block"
                        >
                            <img
                                src="<?= htmlspecialchars(
                                    $imagenNoticia,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                onerror="this.onerror=null;this.src='assets/images/video.jpg';"
                                alt="<?= htmlspecialchars(
                                    (string)$noticia['titulo'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="img-fluid noticia-admin-img"
                            >
                        </a>

                        <div class="blog-info">

                            <?php if ($fechaNoticia !== ''): ?>
                                <h5>
                                    <?= htmlspecialchars(
                                        $fechaNoticia,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h5>
                            <?php endif; ?>

                            <h4>
                                <a
                                    target="_blank"
                                    href="<?= htmlspecialchars(
                                        $urlNoticia,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="d-block"
                                >
                                    <?= htmlspecialchars(
                                        (string)$noticia['titulo'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </a>
                            </h4>

                            <a
                                target="_blank"
                                href="<?= htmlspecialchars(
                                    $urlNoticia,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="btn mt-4 p-0"
                            >
                                Leer
                                <span class="fa fa-arrow-right"></span>
                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>


                <div class="col-lg-4 col-md-6 grids5-info">
                    <a target="_blank" href="https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg" class="d-block"><img src="assets/images/nota-facebook-21-11-25.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/nota-facebook-21-11-25.png';" alt=""class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Noviembre 21, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <h4><a target="_blank" href="https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg" class="d-block">Impulsan talento local en Hualgayoc</a></h4>
                        <a target="_blank" href="https://minart.pe/2025/11/07/gold-fields-y-empresas-locales-apuestan-por-el-talento-hualgayoquino-capacitando-a-pobladores-en-manejo-de-camiones-mineros-en-hualgayoc/?fbclid=IwY2xjawON3L1leHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpzyjGhuFRl3v4LIaB0ks6cftrL-zGT73DnNcsALEsgAQZRUufUc4iTcrLEc_aem_hYAx1MhXJflxvlIajOunzg" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-lg-0 mt-5">
                    <a target="_blank" href="https://andina.pe/agencia/noticia-canete-inauguran-moderno-local-colegio-construido-inversion-s30-millones-1051936.aspx" class="d-block"><img src="assets/images/nota-facebook-21-11-25b.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/nota-facebook-21-11-25b.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Noviembre 21, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <h4><a target="_blank" href="https://andina.pe/agencia/noticia-canete-inauguran-moderno-local-colegio-construido-inversion-s30-millones-1051936.aspx" class="d-block">Inauguran moderno colegio en Cerro Azul</a></h4>
                        <a target="_blank" href="https://andina.pe/agencia/noticia-canete-inauguran-moderno-local-colegio-construido-inversion-s30-millones-1051936.aspx" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-md-0 mt-5">
                    <a target="_blank" href="https://diarioelnoticiero.com/ministerio-de-vivienda-llego-a-juliaca-para-reafirmar-que-el-proyecto-de-agua-potable-y-alcantarillado-no-se-detiene-2/?fbclid=IwY2xjawON3QpleHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHneyK2etohVG6KyvDXFJM_GtKA_gWI85gaZ5yLBuK66R0SW80sBUUdbwRCjt_aem_ByzW0vi0q7VetPB26LXGBg" class="d-block"><img src="assets/images/nota-facebook-20-11-25.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/nota-facebook-20-11-25.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Noviembre 20, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <h4><a target="_blank" href="https://diarioelnoticiero.com/ministerio-de-vivienda-llego-a-juliaca-para-reafirmar-que-el-proyecto-de-agua-potable-y-alcantarillado-no-se-detiene-2/?fbclid=IwY2xjawON3QpleHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHneyK2etohVG6KyvDXFJM_GtKA_gWI85gaZ5yLBuK66R0SW80sBUUdbwRCjt_aem_ByzW0vi0q7VetPB26LXGBg" class="d-block">Megaproyecto de saneamiento en Juliaca</a></h4>
                        <a target="_blank" href="https://diarioelnoticiero.com/ministerio-de-vivienda-llego-a-juliaca-para-reafirmar-que-el-proyecto-de-agua-potable-y-alcantarillado-no-se-detiene-2/?fbclid=IwY2xjawON3QpleHRuA2FlbQIxMABicmlkETFjbkNQNVZ0NVN4WVhmekpIc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHneyK2etohVG6KyvDXFJM_GtKA_gWI85gaZ5yLBuK66R0SW80sBUUdbwRCjt_aem_ByzW0vi0q7VetPB26LXGBg" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <!--<div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block"><img src="assets/images/blog8.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/blog8.jpg';" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block">Experience the breathtaking views and perspectives</a>
                        </h4>
                        <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block"><img src="assets/images/blog9.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/blog9.jpg';" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block">The absolute best foods for getting that youthful glow</a>
                        </h4>
                        <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block"><img src="assets/images/blog.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/blog.jpg';" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block">Our quiet not heart along scale sense timed practice</a>
                        </h4>
                        <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block"><img src="assets/images/blog1.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/blog1.jpg';" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block">Increasing your advantage by aligning strategy.</a>
                        </h4>
                        <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block"><img src="assets/images/blog2.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/blog2.jpg';" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block">Business performance, Design incubator </a>
                        </h4>
                        <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block"><img src="assets/images/blog4.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/blog4.jpg';" alt=""
                            class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Posted on May 20, 2022</h5>
                        <ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>
                        <h4><a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="d-block">Preparing for a new global economy</a>
                        </h4>
                        <a href="https://www.dialogoydesarrollo.com.pe/blog-single.html" class="btn mt-4 p-0">Read More <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>-->
            </div>
            <div class="pagination">
                <ul>
                    <li><a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru">Ver todos</a></li>
                </ul>
            </div>
        </div>
</div>
<!-- // grids block 5 --

<!-- middle grid -->
<section class="w3l-homeblock5 py-0">
    <div class="container py-lg-5 py-4">
        <div class="row">
            <div class="col-lg-8 align-self">
                <h3 class="title-big mb-4"> Boletin NTEP Año 2025 </h3>
                <p class="">-Promueven megaproyectos turísticos por S/ 2,400 mllns.</p>
                <p class="">-Invertirán S/ 9 millones en zonas rurales de Cusco.</p>
                <p class="">-Producción láctea se duplica en Cajamarca.</p>
                <div class="row mt-sm-4 mt-2 px-3">
                    <div class="col-6 p-0">
                        <span>Nº 45</span>
                        <h4>28 agosto</h4>
                    </div>
                    <div class="col-6 p-0">
                        <span><a target="_blank" href="boletines/boletin-NTEP-edicion-N45-2808.pdf" class="facebook"><span class="fa fa-download"></span></a></span>
                        <h4>Ver Boletin</h4>
                    </div>
                    <center><a href="boletines.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
                </div>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4">
                <img src="assets/images/boletin-ntep-45.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/boletin-ntep-45.png';" class="img-fluid radius-image" alt="">
            </div>
        </div>
    </div>
</section>
<!-- //middle grid -->
<?php if (!empty($podcastsInicio)): ?>

<section class="w3l-homeblock3 py-5">
    <div class="container py-lg-5 py-md-4">

        <!--<h5 class="title-small mb-1 text-center">12 speakers and 20 fun events.</h5>-->
        <h3 class="title-big mb-5 text-center">Podcast</h3>

        <div class="row">

            <?php foreach ($podcastsInicio as $i => $podcast): ?>

                <?php
                    $portadaPodcast = 'assets/images/podcast.png';

                    if (!empty($podcast['portada'])) {
                        $nombrePortadaPodcast =
                            basename((string)$podcast['portada']);

                        $rutaPortadaPodcast =
                            __DIR__
                            . '/admin/uploads/podcasts/'
                            . $nombrePortadaPodcast;

                        if (is_file($rutaPortadaPodcast)) {
                            $portadaPodcast =
                                'admin/uploads/podcasts/'
                                . rawurlencode($nombrePortadaPodcast);
                        }
                    }

                    if ($i === 0) {
                        $clasePodcast = '';
                    } elseif ($i === 1) {
                        $clasePodcast = 'mt-sm-0 mt-5';
                    } else {
                        $clasePodcast = 'mt-lg-0 mt-5';
                    }
                ?>

                <div class="col-lg-3 col-sm-6 <?= $clasePodcast ?>">

                    <div class="area-box">

                        <a href="podcasts.php">
                            <img
                                src="<?= htmlspecialchars(
                                    $portadaPodcast,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                onerror="this.onerror=null;this.src='assets/images/podcast.png';"
                                alt="<?= htmlspecialchars(
                                    (string)($podcast['titulo'] ?? 'Podcast'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                        </a>

                        <p>
                            <?= htmlspecialchars(
                                (string)($podcast['titulo'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <center>
            <a
                href="podcasts.php"
                class="btn btn-style btn-primary mt-md-5 mt-4"
            >
                Ver todos
            </a>
        </center>

    </div>
</section>

<?php else: ?>

<section class="w3l-homeblock3 py-5">
    <div class="container py-lg-5 py-md-4">
        <!--<h5 class="title-small mb-1 text-center">12 speakers and 20 fun events.</h5>-->
        <h3 class="title-big mb-5 text-center">Podcast</h3>
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="area-box">
                    <img src="assets/images/podcast.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/podcast.png';">
                    <!--<h4><a href="#feature" class="title-head">Technology</a></h4>-->
                    <p>Aumentan casos de hackeo de WhatsApp y delitos informáticos en el país.</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mt-sm-0 mt-5">
                <div class="area-box">
                    <img src="assets/images/podcast.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/podcast.png';">
                    <!--<h4><a href="#feature" class="title-head">Technology</a></h4>-->
                    <p>Ministerio Público exige mayor presupuesto para la lucha contra las extorsiones.</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mt-lg-0 mt-5">
                <div class="area-box">
                    <img src="assets/images/podcast.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/podcast.png';">
                    <!--<h4><a href="#feature" class="title-head">Technology</a></h4>-->
                    <p>Vivamus a ligula quam. elit leo blandit sed eu  non ipsum dolor, sed dolor amet laoreet.</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mt-lg-0 mt-5">
                <div class="area-box">
                    <img src="assets/images/podcast.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/podcast.png';">
                    <!--<h4><a href="#feature" class="title-head">Technology</a></h4>-->
                    <p>Vivamus a ligula quam. elit leo blandit sed eu  non ipsum dolor, sed dolor amet laoreet.</p>
                </div>
            </div>
        </div>
        <center><a href="podcasts.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
    </div>
</section>

<?php endif; ?>


<!-- logos Section --
<section class="w3l-logos w3l-homeblock3 py-5">
    <div class="container py-lg-3">
        <h5 class="title-small mb-1 text-center">DyD Perú</h5>
        <h3 class="title-big mb-md-5 mb-4 text-center">Alianzas</h3>
        <div class="row">
            <div class="col-lg-12 mx-auto">
                <div class="owl-logos owl-carousel owl-theme logo-view">
                    <div class="item">
                        <img src="assets/images/logo1.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo1.png';" alt="company-logo radius-image" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="assets/images/logo2.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo2.png';" alt="company-logo radius-image" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="assets/images/logo3.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo3.png';" alt="company-logo radius-image" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="assets/images/logo4.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo4.png';" alt="company-logo radius-image" class="img-fluid">

                    </div>
                    <div class="item">
                        <img src="assets/images/logo5.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo5.png';" alt="company-logo radius-image" class="img-fluid">

                    </div>
                    <div class="item">
                        <img src="assets/images/logo6.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/logo6.png';" alt="company-logo radius-image" class="img-fluid">

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- //logos Section -->

<?php if (!empty($videosInicio)): ?>

<section class="w3l-team" id="team">
    <div class="teams1 py-5 mb-3">
        <div class="container py-lg-3 pb-lg-5 pb-4">
            <div class="teams1-content">

                <h3 class="title-big text-center mb-5">
                    Especiales
                </h3>

                <div class="owl-carousel owl-theme text-center">

                    <?php foreach ($videosInicio as $video): ?>

                        <?php
                            $embedVideo =
                                videoEmbedInicio(
                                    $video['url_embed'] ?? ''
                                );
                        ?>

                        <div class="item">

                            <div class="d-grid team-info">

                                <div class="column position-relative">

                                    <?php if ($embedVideo !== ''): ?>

                                        <div
                                            style="
                                                position:relative;
                                                width:100%;
                                                padding-top:56.25%;
                                                overflow:hidden;
                                                border-radius:8px;
                                                background:#000;
                                            "
                                        >

                                            <iframe
                                                src="<?= htmlspecialchars(
                                                    $embedVideo,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                title="<?= htmlspecialchars(
                                                    (string)$video['titulo'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                allow="
                                                    accelerometer;
                                                    autoplay;
                                                    clipboard-write;
                                                    encrypted-media;
                                                    gyroscope;
                                                    picture-in-picture
                                                "
                                                allowfullscreen
                                                style="
                                                    position:absolute;
                                                    inset:0;
                                                    width:100%;
                                                    height:100%;
                                                    border:0;
                                                "
                                            ></iframe>

                                        </div>

                                    <?php endif; ?>

                                </div>

                                <div class="column mt-3">

                                    <p>
                                        <?= htmlspecialchars(
                                            (string)$video['titulo'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>
        </div>
    </div>
</section>

<?php else: ?>

<section class="w3l-team" id="team">
    <div class="teams1 py-5 mb-3">
        <div class="container py-lg-3 pb-lg-5 pb-4">
            <div class="teams1-content">
                <!--<h5 class="title-small text-center">Amazing speakers</h5>-->
                <h3 class="title-big text-center mb-5">Especiales</h3>
                    <div class="owl-carousel owl-theme text-center">
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team2.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team2.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <!--<h3 class="name-pos"><a href="#url">Anthony</a></h3>-->
                                    <p>Por una mineria artesanal segura para todos</p>
                                    <!--<div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team3.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team3.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <!--<h3 class="name-pos"><a href="#url">Sara grant</a></h3>-->
                                    <p>REINFO Días decisivos en el Congreso</p>
                                    <!--<div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team4.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team4.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <!--<h3 class="name-pos"><a href="#url">Claire Olson</a></h3>-->
                                    <p>La minería ilegal: un negocio rentable para bandas criminales</p>
                                    <!--<div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team5.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team5.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <!--<h3 class="name-pos"><a href="#url">Paula cross</a></h3>-->
                                    <p>El problema del REINFO y la minería ilegal en 50 segundos</p>
                                    <!--<div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <!--<div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team6.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team6.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <h3 class="name-pos"><a href="#url">Amber kinsa</a></h3>
                                    <p>CEO of company</p>
                                    <div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team7.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team7.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <h3 class="name-pos"><a href="#url">Edward wood</a></h3>
                                    <p>Manager & Chief</p>
                                    <div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team8.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team8.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <h3 class="name-pos"><a href="#url">Jonarthan parks</a></h3>
                                    <p>Manager and Officer</p>
                                    <div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/s1.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/s1.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <h3 class="name-pos"><a href="#url">Leroy bell</a></h3>
                                    <p>CEO of company</p>
                                    <div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <a href="#url"><img src="assets/images/team1.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/team1.jpg';" alt="" class="img-fluid rounded team-image" /></a>
                                </div>
                                <div class="column">
                                    <h3 class="name-pos"><a href="#url">Bradley</a></h3>
                                    <p>Founder of Company</p>
                                    <div class="social">
                                        <a href="#facebook" class="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a>
                                        <a href="#twitter" class="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a>
                                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>-->
                    </div>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>
<section class="w3l-banner py-0" id="work">
    <div class="midd-w3 py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mt-lg-0 mt-lg-5 about-right-faq align-self">
                    <h5 class="title-small mb-2">DDP Noticias</h5>
                    <h3 class="title-banner">Diálogo y Desarrollo Perú</h3>
                    <p class="mt-4">Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
                        <a href="#btn" class="btn btn-style btn-primary mt-md-5 mt-4">Nosotros</a>
                 </div>
                <div class="col-md-6 left-wthree-img mt-lg-0 mt-4">
                    <div class="position-relative">
                        <img src="assets/images/bannerimg.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/bannerimg.jpg';" alt="" class="img-fluid">
                        <!--<a href="#small-dialog" class="popup-with-zoom-anim play-view text-center position-absolute">
                            <span class="video-play-icon">
                                <span class="fa fa-play"></span>
                            </span>
                        </a>
                         dialog itself, mfp-hide class is required to make dialog hidden -->
                        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
                            <iframe src="https://www.youtube.com/embed/2jI6fHBtRJU" allow="autoplay; fullscreen" allowfullscreen=""></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- middle grid --
<section class="w3l-homeblock5 py-5">
    <div class="container py-lg-5 py-4">
        <div class="row">
            <div class="col-lg-6 align-self">
                <h3 class="title-big mb-4"> Don’t miss out on the fun and join the community! </h3>
                <p class="">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptates maiores ipsum quos
                    voluptate, cumque perspiciatis dolorem tempora fugit facere ducimus?.</p>
                <div class="row mt-sm-4 mt-2 px-3">
                    <div class="col-6 p-0">
                        <span>80+</span>
                        <h4>Speakers</h4>
                    </div>
                    <div class="col-6 p-0">
                        <span>50+</span>
                        <h4>Workshops</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-lg-0 mt-4">
                <img src="assets/images/stats.jpg" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/stats.jpg';" class="img-fluid radius-image" alt="">
            </div>
        </div>
    </div>
</section>
<!-- //middle grid -->
<!-- middle -->
<div class="middle py-5">
    <div class="container py-xl-5 py-lg-3">
        <div class="welcome-left text-center py-md-5 py-3">
            <h3 class="title-big">Síguenos en nuestras Redes Sociales</h3>
            <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square fa-2x"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokg.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/tiktokg.png';"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram fa-2x"></span></a>
            <!--<a href="#youtube" class="youtube"><span class="fa fa-youtube fa-2x"></span></a>
            <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin fa-2x"></span></a>-->
          </div>
        </div>
    </div>
</div>
<!-- //middle -->


<!-- footer block -->
<section class="w3l-footer-29-main py-5" id="footer">
  <div class="footer-29 py-md-3">
    <div class="container">
      <div class="row footer-top-29">
        <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
          <h6 class="footer-title-29">Quiénes Somos</h6>
          <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
          <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokp.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/tiktokp.png';"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram"></span></a>
            <!--<a href="#youtube" class="youtube"><span class="fa fa-youtube"></span></a>
            <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin"></span></a>-->
          </div>
        </div>
        <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">
          <ul>
            <h6 class="footer-title-29">Contenido</h6>
            <li><a href="#url">Noticias</a></li>
            <li><a href="#url">Videos</a></li>
            <li><a href="#url">Posdcast.</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
          <div class="properties">
            <h6 class="footer-title-29">Contacto</h6>
            <ul>
            <!--<!--<li><a href="#url">Celulares</a></li>-->
            <!--<li><a href="#url">Celulares</a></li>-->
            <li><a href="#url">info@dialogoydesarrollo.com.pe</a></li>
          </ul>
          </div>
        </div>
      </div>
      <div class="bottom-copies text-center">
            <p class="copy-footer-29">© 2026 Diálogo y Desarrollo Perú. All rights reserved | Designed by <a target="_blank" href="https://www.wsperu.info">WebSolutions</a></p>
        </div>
    </div>
  </div>
  <!-- move top -->
  <button onclick="topFunction()" id="movetop" title="Go to top">
    <span class="fa fa-angle-up"></span>
  </button>
  <script>
    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function () {
      scrollFunction()
    };

    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("movetop").style.display = "block";
      } else {
        document.getElementById("movetop").style.display = "none";
      }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>
  <!-- /move top -->
</section>
<!-- //footer block -->

<!-- Template JavaScript -->
<script src="assets/js/jquery-3.3.1.min.js"></script>

<script src="assets/js/theme-change.js"></script><!-- theme switch js (light and dark)-->

<!-- responsive tabs -->
<script src="assets/js/easyResponsiveTabs.js"></script>
<!--Plug-in Initialisation-->
<script type="text/javascript">
  $(document).ready(function () {
    //Horizontal Tab
    $('#parentHorizontalTab').easyResponsiveTabs({
      type: 'default', //Types: default, vertical, accordion
      width: 'auto', //auto or any width like 600px
      fit: true, // 100% fit in a container
      tabidentify: 'hor_1', // The tab groups identifier
      activate: function (event) { // Callback function if tab is switched
        var $tab = $(this);
        var $info = $('#nested-tabInfo');
        var $name = $('span', $info);
        $name.text($tab.text());
        $info.show();
      }
    });
  });
</script>


<script src="assets/js/owl.carousel.js"></script>
<!-- logos for customers -->
<script>
  $(document).ready(function () {
    $('.owl-logos').owlCarousel({
      loop: true,
      margin: 0,
      nav: false,
      responsiveClass: true,
      autoplay: true,
      autoplayTimeout: 5000,
      autoplaySpeed: 1000,
      autoplayHoverPause: false,
      responsive: {
        0: {
          items: 2,
          nav: false
        },
        480: {
          items: 2,
          nav: false
        },
        568: {
          items: 3,
          nav: false
        },
        1000: {
          items: 5,
          nav: false
        }
      }
    })
  })
</script>
<!-- //logos owlcarousel -->

<!-- for tesimonials carousel slider -->
<script>
  $(document).ready(function () {
    $("#owl-demo1").owlCarousel({
      loop: true,
      margin: 20,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
          nav: true
        },
        768: {
          items: 2,
          nav: false
        },
        1000: {
          items: 3,
          nav: true,
          loop: false
        }
      }
    })
  })
</script>
<!-- //script -->

<!-- script for teams -->
<script>
  $(document).ready(function () {
    $('.owl-carousel').owlCarousel({
      loop: true,
      margin: 0,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
          nav: true
        },
        400: {
          items: 2,
          nav: true,
          margin: 20
        },
        768: {
          items: 3,
          nav: true,
          margin: 20
        },
        1000: {
          items: 4,
          nav: true,
          loop: true,
          margin: 25
        }
      }
    })
  })
</script>
<!-- //script for teams-->

<!-- Script for counter -->
<script>
  (() => {
    // Specify the deadline date
    const deadlineDate = new Date('January 27, 2025 23:59:59').getTime();

    // Cache all countdown boxes into consts
    const countdownDays = document.querySelector('.countdown__days .number');
    const countdownHours = document.querySelector('.countdown__hours .number');
    const countdownMinutes = document.querySelector('.countdown__minutes .number');
    const countdownSeconds = document.querySelector('.countdown__seconds .number');

    // Update the count down every 1 second (1000 milliseconds)
    setInterval(() => {
      // Get current date and time
      const currentDate = new Date().getTime();

      // Calculate the distance between current date and time and the deadline date and time
      const distance = deadlineDate - currentDate;

      // Calculations the data for remaining days, hours, minutes and seconds
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Insert the result data into individual countdown boxes
      countdownDays.innerHTML = days;
      countdownHours.innerHTML = hours;
      countdownMinutes.innerHTML = minutes;
      countdownSeconds.innerHTML = seconds;
    }, 1000);
  })();
</script>
<!-- //Script for counter -->

<script src="assets/js/jquery.magnific-popup.min.js"></script>
<script>
  $(document).ready(function () {
    $('.popup-with-zoom-anim').magnificPopup({
      type: 'inline',

      fixedContentPos: false,
      fixedBgPos: true,

      overflowY: 'auto',

      closeBtnInside: true,
      preloader: false,

      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-zoom-in'
    });

    $('.popup-with-move-anim').magnificPopup({
      type: 'inline',

      fixedContentPos: false,
      fixedBgPos: true,

      overflowY: 'auto',

      closeBtnInside: true,
      preloader: false,

      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-slide-bottom'
    });
  });
</script>

<!-- disable body scroll which navbar is in active -->
<script>
  $(function () {
    $('.navbar-toggler').click(function () {
      $('body').toggleClass('noscroll');
    })
  });
</script>
<!-- disable body scroll which navbar is in active -->

<!--/MENU-JS-->
<script>
  $(window).on("scroll", function () {
    var scroll = $(window).scrollTop();

    if (scroll >= 80) {
      $("#site-header").addClass("nav-fixed");
    } else {
      $("#site-header").removeClass("nav-fixed");
    }
  });

  //Main navigation Active Class Add Remove
  $(".navbar-toggler").on("click", function () {
    $("header").toggleClass("active");
  });
  $(document).on("ready", function () {
    if ($(window).width() > 991) {
      $("header").removeClass("active");
    }
    $(window).on("resize", function () {
      if ($(window).width() > 991) {
        $("header").removeClass("active");
      }
    });
  });
</script>
<!--//MENU-JS-->

<script src="assets/js/bootstrap.min.js"></script>

</body>

</html>