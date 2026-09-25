<?php
require_once __DIR__ . '/admin/config/conexion.php';
$pdo = db();

/* Detectar columnas reales de la tabla alianzas */
$cols = [];
try {
    $stmtCols = $pdo->query("SHOW COLUMNS FROM alianzas");
    foreach ($stmtCols->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $cols[$c['Field']] = true;
    }
} catch (Throwable $e) {
    $cols = [];
}

/* Elegir nombres existentes sin asumir estructura */
function colExiste(array $cols, array $opciones): ?string {
    foreach ($opciones as $op) {
        if (isset($cols[$op])) return $op;
    }
    return null;
}

$idCol     = colExiste($cols, ['id','id_alianza']);
$nombreCol = colExiste($cols, ['nombre','titulo','nombre_alianza']);
$descCol   = colExiste($cols, ['descripcion','resumen','detalle','contenido']);
$logoCol   = colExiste($cols, ['logo','imagen','foto','imagen_logo']);
$linkCol   = colExiste($cols, ['enlace','url','link','sitio_web']);
$estadoCol = colExiste($cols, ['estado']);
$ordenCol  = colExiste($cols, ['orden']);

$alianzas = [];

if ($cols && $nombreCol) {
    $select = [];
    if ($idCol)     $select[] = "`$idCol` AS id";
    else            $select[] = "0 AS id";

    $select[] = "`$nombreCol` AS nombre";
    $select[] = $descCol ? "`$descCol` AS descripcion" : "'' AS descripcion";
    $select[] = $logoCol ? "`$logoCol` AS logo" : "'' AS logo";
    $select[] = $linkCol ? "`$linkCol` AS enlace" : "'' AS enlace";
    $select[] = $estadoCol ? "`$estadoCol` AS estado" : "'publicado' AS estado";
    $select[] = $ordenCol ? "`$ordenCol` AS orden" : "0 AS orden";

    $sql = "SELECT " . implode(", ", $select) . " FROM alianzas";

    if ($estadoCol) {
        $sql .= " WHERE `$estadoCol` = 'publicado'";
    }

    if ($ordenCol) {
        $sql .= " ORDER BY `$ordenCol` ASC";
        if ($idCol) $sql .= ", `$idCol` DESC";
    } elseif ($idCol) {
        $sql .= " ORDER BY `$idCol` DESC";
    }

    $alianzas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function rutaLogoAlianza(string $valor): string {
    $valor = trim($valor);
    if ($valor === '') return 'assets/images/logo.png';

    $nombre = basename($valor);

    $candidatos = [
        [__DIR__ . '/admin/uploads/alianzas/' . $nombre, 'admin/uploads/alianzas/' . rawurlencode($nombre)],
        [__DIR__ . '/uploads/alianzas/' . $nombre, 'uploads/alianzas/' . rawurlencode($nombre)],
        [__DIR__ . '/assets/images/' . $nombre, 'assets/images/' . rawurlencode($nombre)],
    ];

    foreach ($candidatos as [$fisica, $publica]) {
        if (is_file($fisica)) return $publica;
    }

    if (preg_match('~^https?://~i', $valor)) return $valor;

    return 'assets/images/logo.png';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Alianzas - Diálogo y Desarrollo Perú</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <style>
        .alianzas-wrap{background:#f7f8fb}
        .alianza-card{background:#fff;border-radius:14px;padding:22px;height:100%;box-shadow:0 5px 20px rgba(0,0,0,.07)}
        .alianza-logo{width:100%;height:150px;object-fit:contain;padding:10px;margin-bottom:15px}
        .alianza-card h4{font-size:19px;font-weight:600;margin-bottom:10px}
        .alianza-card p{font-size:14px;line-height:1.6;color:#666}
        .alianza-link{font-weight:600;color:#e50914}
    </style>
</head>
<body>

<header id="site-header" class="fixed-top">
  <div class="container">
    <nav class="navbar navbar-expand-lg stroke">
      <a class="navbar-brand" href="index.php"><img src="assets/images/logo.png" alt="DDP" style="height:75px;"></a>
      <button class="navbar-toggler collapsed bg-gradient" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02">
        <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
        <span class="navbar-toggler-icon fa icon-close fa-times"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#actualidad">Actualidad</a></li>
          <li class="nav-item"><a class="nav-link" href="reportajes.php">Reportajes</a></li>
          <li class="nav-item"><a class="nav-link" href="podcasts.php">Podcast</a></li>
          <li class="nav-item"><a class="nav-link" href="boletines.php">Boletín NTEP</a></li>
          <li class="nav-item active"><a class="nav-link" href="alianzas.php">Alianzas</a></li>
          <li class="nav-item"><a class="nav-link" href="sobre-dd.php">Sobre D&D</a></li>
          <li class="ml-2"><a href="contacto.php" class="btn btn-style btn-outline-secondary">Contacto</a></li>
        </ul>
      </div>
    </nav>
  </div>
</header>

<section class="breadcrumb-area py-sm-5 py-4">
  <div class="container">
    <div class="breadcrumb-contents">
      <h2 class="title-big">Alianzas</h2>
      <p class="mt-2">Instituciones y organizaciones que colaboran con Diálogo y Desarrollo Perú.</p>
    </div>
  </div>
</section>

<section class="alianzas-wrap py-5">
  <div class="container py-lg-4">
    <h5 class="title-small mb-1 text-center">Diálogo y Desarrollo Perú</h5>
    <h3 class="title-big mb-5 text-center">Nuestras Alianzas</h3>

    <div class="row">
      <?php if (!$cols): ?>
        <div class="col-12 text-center py-5">
          <h4>No se encontró la tabla alianzas.</h4>
        </div>
      <?php elseif (!$nombreCol): ?>
        <div class="col-12 text-center py-5">
          <h4>La tabla alianzas existe, pero no tiene una columna de nombre/título reconocida.</h4>
        </div>
      <?php elseif (!$alianzas): ?>
        <div class="col-12 text-center py-5">
          <h4>Aún no hay alianzas publicadas.</h4>
        </div>
      <?php endif; ?>

      <?php foreach ($alianzas as $a): ?>
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
          <article class="alianza-card">
            <img class="alianza-logo"
                 src="<?= htmlspecialchars(rutaLogoAlianza((string)$a['logo']), ENT_QUOTES, 'UTF-8') ?>"
                 onerror="this.onerror=null;this.src='assets/images/logo.png';"
                 alt="<?= htmlspecialchars((string)$a['nombre'], ENT_QUOTES, 'UTF-8') ?>">

            <h4><?= htmlspecialchars((string)$a['nombre'], ENT_QUOTES, 'UTF-8') ?></h4>

            <?php if (!empty($a['descripcion'])): ?>
              <p><?= nl2br(htmlspecialchars((string)$a['descripcion'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>

            <?php if (!empty($a['enlace'])): ?>
              <a class="alianza-link" target="_blank" rel="noopener"
                 href="<?= htmlspecialchars((string)$a['enlace'], ENT_QUOTES, 'UTF-8') ?>">
                Visitar sitio <span class="fa fa-external-link"></span>
              </a>
            <?php endif; ?>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script src="assets/js/jquery-3.3.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
