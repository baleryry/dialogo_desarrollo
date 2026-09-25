<?php
require_once __DIR__ . '/admin/config/conexion.php';

$pdo = db();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asunto = trim($_POST['asunto'] ?? '');
    $contenido = trim($_POST['mensaje'] ?? '');

    if (
        $nombre === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $contenido === ''
    ) {
        $error = 'Completa correctamente los campos obligatorios.';
    } else {
        /*
         * Formulario listo para demostración local.
         * Aquí más adelante se puede conectar con correo o base de datos.
         */
        $mensaje = 'Gracias por escribirnos. Tu mensaje fue recibido correctamente.';
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

    <title>Contacto - Diálogo y Desarrollo Perú</title>

    <link
        href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&subset=latin-ext,vietnamese"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style-starter.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    >

    <style>
        .contact-page {
            background: #f7f8fa;
        }

        .contact-card,
        .contact-info-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
            height: 100%;
        }

        .contact-card {
            padding: 34px;
        }

        .contact-info-card {
            padding: 34px;
        }

        .contact-info-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 26px;
        }

        .contact-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 50%;
            background: #df0a11;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .contact-info-item h5 {
            margin: 0 0 5px;
            color: #20242a;
            font-weight: 600;
        }

        .contact-info-item p,
        .contact-info-item a {
            color: #777;
            margin: 0;
        }

        .contact-form .form-control {
            min-height: 50px;
            border-radius: 8px;
            border: 1px solid #dcdfe4;
            box-shadow: none;
        }

        .contact-form textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .contact-form .form-control:focus {
            border-color: #df0a11;
        }

        .contact-submit {
            background: #df0a11;
            border-color: #df0a11;
            color: #fff;
        }

        .contact-submit:hover {
            background: #bd080e;
            border-color: #bd080e;
            color: #fff;
        }

        .contact-social a {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f2f4;
            color: #333;
            margin-right: 8px;
            transition: .2s ease;
        }

        .contact-social a:hover {
            background: #df0a11;
            color: #fff;
        }

        @media (max-width: 767px) {
            .contact-card,
            .contact-info-card {
                padding: 24px;
            }
        }
    </style>

</head>

<body>

<!-- HEADER -->
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

            <div
                class="collapse navbar-collapse"
                id="navbarTogglerDemo02"
            >
                <ul class="navbar-nav ml-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            Inicio
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="index.php#actualidad">
                            Actualidad
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="reportajes.php">
                            Reportajes
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="podcasts.php">
                            Podcast
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="boletines.php">
                            Boletín NTEP
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="alianzas.php">
                            Alianzas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="sobre-dd.php">
                            Sobre D&D
                        </a>
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
<!-- /HEADER -->


<!-- TÍTULO -->
<section class="breadcrumb-area py-sm-5 py-4">
    <div class="container">
        <div class="row">

            <div class="col-md-12">

                <div class="breadcrumb-contents">

                    <h2 class="title-big">
                        Contacto
                    </h2>

                    <p class="mt-2">
                        Escríbenos. Queremos conocer tus consultas,
                        propuestas y comentarios.
                    </p>

                </div>

            </div>

        </div>
    </div>
</section>
<!-- /TÍTULO -->


<!-- CONTACTO -->
<section class="contact-page py-5">

    <div class="container py-lg-5 py-md-4">

        <div class="text-center mb-5">
            <h5 class="title-small mb-1">
                Diálogo y Desarrollo Perú
            </h5>

            <h3 class="title-big">
                Conversemos
            </h3>

            <p class="mt-3">
                Puedes comunicarte con nosotros mediante el formulario
                o a través de nuestros canales digitales.
            </p>
        </div>


        <div class="row g-4">

            <!-- INFORMACIÓN -->
            <div class="col-lg-5 mb-4 mb-lg-0">

                <div class="contact-info-card">

                    <h3 class="mb-4">
                        Información de contacto
                    </h3>

                    <div class="contact-info-item">

                        <div class="contact-icon">
                            <span class="fa fa-envelope"></span>
                        </div>

                        <div>
                            <h5>Correo electrónico</h5>

                            <a href="mailto:info@dialogoydesarrollo.com.pe">
                                info@dialogoydesarrollo.com.pe
                            </a>
                        </div>

                    </div>


                    <div class="contact-info-item">

                        <div class="contact-icon">
                            <span class="fa fa-map-marker"></span>
                        </div>

                        <div>
                            <h5>Ubicación</h5>
                            <p>Perú</p>
                        </div>

                    </div>


                    <div class="contact-info-item">

                        <div class="contact-icon">
                            <span class="fa fa-comments"></span>
                        </div>

                        <div>
                            <h5>Consultas y propuestas</h5>
                            <p>
                                Periodismo, diálogo, desarrollo,
                                alianzas y colaboraciones.
                            </p>
                        </div>

                    </div>


                    <hr>


                    <h5 class="mb-3">
                        Síguenos
                    </h5>

                    <div class="contact-social">

                        <a
                            target="_blank"
                            href="https://www.facebook.com/DialogoyDesarrolloPeru"
                            aria-label="Facebook"
                        >
                            <span class="fa fa-facebook"></span>
                        </a>

                        <a
                            target="_blank"
                            href="https://www.instagram.com/dialogo.y.desarrollo/"
                            aria-label="Instagram"
                        >
                            <span class="fa fa-instagram"></span>
                        </a>

                        <a
                            target="_blank"
                            href="https://www.tiktok.com/@dialogo.y.desarrollo"
                            aria-label="TikTok"
                        >
                            <span class="fa fa-music"></span>
                        </a>

                    </div>

                </div>

            </div>


            <!-- FORMULARIO -->
            <div class="col-lg-7">

                <div class="contact-card">

                    <h3 class="mb-4">
                        Envíanos un mensaje
                    </h3>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">
                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                    <?php endif; ?>


                    <?php if ($mensaje): ?>

                        <div class="alert alert-success">
                            <?= htmlspecialchars(
                                $mensaje,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                    <?php endif; ?>


                    <form
                        method="post"
                        class="contact-form"
                    >

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label>
                                    Nombre *
                                </label>

                                <input
                                    type="text"
                                    name="nombre"
                                    class="form-control"
                                    placeholder="Tu nombre"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label>
                                    Correo electrónico *
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="correo@ejemplo.com"
                                    required
                                >

                            </div>

                        </div>


                        <div class="mb-3">

                            <label>
                                Asunto
                            </label>

                            <input
                                type="text"
                                name="asunto"
                                class="form-control"
                                placeholder="¿Sobre qué deseas escribirnos?"
                            >

                        </div>


                        <div class="mb-4">

                            <label>
                                Mensaje *
                            </label>

                            <textarea
                                name="mensaje"
                                class="form-control"
                                placeholder="Escribe tu mensaje..."
                                required
                            ></textarea>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-style contact-submit"
                        >
                            Enviar mensaje
                            <span class="fa fa-paper-plane ml-1"></span>
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>
<!-- /CONTACTO -->


<!-- REDES -->
<div class="middle py-5">

    <div class="container py-xl-5 py-lg-3">

        <div class="welcome-left text-center py-md-5 py-3">

            <h3 class="title-big">
                Síguenos en nuestras Redes Sociales
            </h3>

            <div class="main-social-footer-29">

                <a
                    target="_blank"
                    href="https://www.facebook.com/DialogoyDesarrolloPeru"
                    class="facebook"
                >
                    <span class="fa fa-facebook-square fa-2x"></span>
                </a>

                <a
                    target="_blank"
                    href="https://www.instagram.com/dialogo.y.desarrollo/"
                    class="instagram"
                >
                    <span class="fa fa-instagram fa-2x"></span>
                </a>

            </div>

        </div>

    </div>

</div>
<!-- /REDES -->


<!-- FOOTER -->
<section
    class="w3l-footer-29-main py-5"
    id="footer"
>

    <div class="footer-29 py-md-3">

        <div class="container">

            <div class="row footer-top-29">

                <div class="col-lg-6 col-md-6 footer-list-29 footer-1">

                    <h6 class="footer-title-29">
                        Quiénes Somos
                    </h6>

                    <p>
                        Somos un espacio de periodismo independiente
                        que busca visibilizar las acciones de diálogo
                        en el país desde una mirada constructiva.
                    </p>

                </div>


                <div class="col-lg-3 col-md-6 footer-list-29 footer-2">

                    <h6 class="footer-title-29">
                        Contenido
                    </h6>

                    <ul>
                        <li>
                            <a href="reportajes.php">
                                Reportajes
                            </a>
                        </li>

                        <li>
                            <a href="podcasts.php">
                                Podcast
                            </a>
                        </li>

                        <li>
                            <a href="boletines.php">
                                Boletines
                            </a>
                        </li>
                    </ul>

                </div>


                <div class="col-lg-3 col-md-6 footer-list-29 footer-3">

                    <h6 class="footer-title-29">
                        Contacto
                    </h6>

                    <ul>
                        <li>
                            <a href="mailto:info@dialogoydesarrollo.com.pe">
                                info@dialogoydesarrollo.com.pe
                            </a>
                        </li>
                    </ul>

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
<!-- /FOOTER -->


<script src="assets/js/jquery-3.3.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>
