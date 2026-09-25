<?php

require_once __DIR__ . '/admin/config/conexion.php';

$pdo = db();

$boletinesNuevos = $pdo->query("
    SELECT
        id,
        numero_boletin,
        resumen,
        foto_portada,
        archivo_pdf,
        fecha_publicacion
    FROM boletines
    ORDER BY fecha_publicacion DESC, id DESC
")->fetchAll(PDO::FETCH_ASSOC);

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

    <style>
      /* Solo para los boletines agregados desde el panel:
         iguala la altura visual con los boletines originales. */
      .boletin-admin-portada {
        width: 100%;
        height: 300px;
        object-fit: cover;
        object-position: center;
      }

      @media (max-width: 767px) {
        .boletin-admin-portada {
          height: auto;
        }
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
                  <li class="nav-item">
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
          <li class="nav-item active">
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
                    <h2 class="title-big">Boletines NTEP</h2>
                    <div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.php">Inicio</a>
                            </li>
                            <li class="active">
                                 Boletines
                            </li>
                        </ul>
                    </div>
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

                <!-- BOLETINES NUEVOS CREADOS DESDE EL PANEL ADMIN -->
                <?php foreach ($boletinesNuevos as $b): ?>
                    <div class="col-lg-4 col-md-6 grids5-info mb-5">

                        <?php if (!empty($b['archivo_pdf'])): ?>
                            <a
                                target="_blank"
                                href="admin/uploads/boletines/<?= rawurlencode($b['archivo_pdf']) ?>"
                                class="d-block"
                            >
                        <?php endif; ?>

                        <?php if (!empty($b['foto_portada'])): ?>
                            <img
                                src="admin/uploads/boletines/<?= rawurlencode($b['foto_portada']) ?>"
                                alt="<?= htmlspecialchars($b['numero_boletin'], ENT_QUOTES, 'UTF-8') ?>"
                                class="img-fluid boletin-admin-portada"
                            >
                        <?php endif; ?>

                        <?php if (!empty($b['archivo_pdf'])): ?>
                            </a>
                        <?php endif; ?>

                        <div class="blog-info">

                            <?php if (!empty($b['numero_boletin'])): ?>
                                <h4 class="mt-3">
                                    <?= htmlspecialchars($b['numero_boletin'], ENT_QUOTES, 'UTF-8') ?>
                                </h4>
                            <?php endif; ?>

                            <h5>
                                <?php
                                if (!empty($b['fecha_publicacion'])) {
                                    echo date('d/m/Y', strtotime($b['fecha_publicacion']));
                                }
                                ?>
                            </h5>

                            <?php if (!empty($b['resumen'])): ?>
                                <p class="mt-3">
                                    <?= nl2br(htmlspecialchars($b['resumen'], ENT_QUOTES, 'UTF-8')) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($b['archivo_pdf'])): ?>
                                <a
                                    target="_blank"
                                    href="admin/uploads/boletines/<?= rawurlencode($b['archivo_pdf']) ?>"
                                    class="btn mt-4 p-0"
                                >
                                    Ver boletín
                                    <span class="fa fa-arrow-right"></span>
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- A PARTIR DE AQUÍ SE MANTIENEN LOS BOLETINES ORIGINALES -->
                <div class="col-lg-4 col-md-6 grids5-info">
                    <a target="_blank" href="boletines/boletin-NTEP-edicion-N45-2808.pdf" class="d-block"><img src="assets/images/boletin-ntep-45.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/boletin-ntep-45.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Ago 28, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <a target="_blank" href="boletines/boletin-NTEP-edicion-N45-2808.pdf" class="btn mt-4 p-0">Ver boletin <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-md-0 mt-5">
                    <a target="_blank" href="boletines/boletin-NTEP-edicion-N44-2508.pdf" class="d-block"><img src="assets/images/boletin-ntep-44.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/boletin-ntep-44.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Ago 25, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <a target="_blank" href="boletines/boletin-NTEP-edicion-N44-2508.pdf" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 grids5-info mt-md-0 mt-5">
                    <a target="_blank" href="boletines/boletin-NTEP-edicion-N43-2108.pdf" class="d-block"><img src="assets/images/boletin-ntep-43.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/boletin-ntep-43.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Ago 21, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <a target="_blank" href="boletines/boletin-NTEP-edicion-N43-2108.pdf" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
        <div class="col-lg-4 col-md-6 grids5-info mt-md-0 mt-5">
                    <a target="_blank" href="boletines/boletin-NTEP-edicion-N42-1808.pdf" class="d-block"><img src="assets/images/boletin-ntep-42.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/boletin-ntep-42.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Ago 18, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <a target="_blank" href="boletines/boletin-NTEP-edicion-N42-1808.pdf" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
        <div class="col-lg-4 col-md-6 grids5-info mt-md-0 mt-5">
                    <a target="_blank" href="boletines/boletin-NTEP-edicion-N41-1408.pdf" class="d-block"><img src="assets/images/boletin-ntep-41.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/boletin-ntep-41.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Ago 14, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <a target="_blank" href="boletines/boletin-NTEP-edicion-N41-1408.pdf" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
        <div class="col-lg-4 col-md-6 grids5-info mt-md-0 mt-5">
                    <a target="_blank" href="boletines/boletin-NTEP-edicion-N40-1108.pdf" class="d-block"><img src="assets/images/boletin-ntep-40.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/boletin-ntep-40.png';" alt="" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5>Ago 11, 2025</h5>
                        <!--<ul class="blog-info">
                            <li><a href="#admin"><span class="fa fa-user"></span> admin</a></li>
                            <li><a href="#comments"><span class="fa fa-comments"></span>3 comments</a></li>
                            <li><a href="#shares"><span class="fa fa-share"></span>3 shares</a></li>
                        </ul>-->
                        <a target="_blank" href="boletines/boletin-NTEP-edicion-N40-1108.pdf" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
            </div>
            <div class="pagination">
                <ul>
                    <li class="prev"><a href="#"> Ant</a></li>
                    <li><a href="#" class="active">1</a></li>
                    <!--<li><a href="reportajes-2.php">2</a></li>
                    <li><a href="#page-number">3</a></li>
                    <li><a href="#page-number">4</a></li>
                    <li><a href="#page-number">5</a></li>-->
                    <li class="next"><a href="reportajes-2.php"> Sig </a></li>
                </ul>
            </div>
        </div>
</div>
<!-- // grids block 5 -->
<!-- footer block -->
<section class="w3l-footer-29-main py-5" id="footer">
  <div class="footer-29 py-md-3">
    <div class="container">
      <div class="row footer-top-29">
        <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
          <h6 class="footer-title-29">Quiénes Somos</h6>
          <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
          <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fas fa-facebook-square"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokp.png" onerror="this.onerror=null;this.src='https://www.dialogoydesarrollo.com.pe/assets/images/tiktokp.png';"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fas fa-instagram"></span></a>
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
      <p class="copy-footer-29">© 2025 Diálogo y Desarrollo Perú. All rights reserved | Designed by <a target="_blank" href="https://www.wsperu.info">WebSolutions</a></p>
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

<!-- js for portfolio lightbox -->
<script src="assets/js/lightbox-plus-jquery.min.js"></script>

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