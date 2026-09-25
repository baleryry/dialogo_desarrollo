<?php
require_once __DIR__ . '/admin/config/conexion.php';
$pdo = db();

function colExisteSobre(array $cols, array $opciones): ?string {
    foreach ($opciones as $op) {
        if (isset($cols[$op])) return $op;
    }
    return null;
}

$cols = [];
$sobre = null;

try {
    $stmtCols = $pdo->query("SHOW COLUMNS FROM sobre_dyd");
    foreach ($stmtCols->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $cols[$c['Field']] = true;
    }
} catch (Throwable $e) {
    $cols = [];
}

if ($cols) {
    $idCol       = colExisteSobre($cols, ['id','id_sobre','id_sobre_dyd']);
    $tituloCol   = colExisteSobre($cols, ['titulo','nombre']);
    $subCol      = colExisteSobre($cols, ['subtitulo','slogan']);
    $contCol     = colExisteSobre($cols, ['contenido','descripcion','texto','resumen']);
    $misionCol   = colExisteSobre($cols, ['mision']);
    $visionCol   = colExisteSobre($cols, ['vision']);
    $imagenCol   = colExisteSobre($cols, ['imagen','foto','portada']);
    $emailCol    = colExisteSobre($cols, ['email','correo']);
    $telCol      = colExisteSobre($cols, ['telefono','celular']);
    $dirCol      = colExisteSobre($cols, ['direccion']);
    $estadoCol   = colExisteSobre($cols, ['estado']);

    $select = [
        $tituloCol ? "`$tituloCol` AS titulo" : "'Diálogo y Desarrollo Perú' AS titulo",
        $subCol ? "`$subCol` AS subtitulo" : "'' AS subtitulo",
        $contCol ? "`$contCol` AS contenido" : "'' AS contenido",
        $misionCol ? "`$misionCol` AS mision" : "'' AS mision",
        $visionCol ? "`$visionCol` AS vision" : "'' AS vision",
        $imagenCol ? "`$imagenCol` AS imagen" : "'' AS imagen",
        $emailCol ? "`$emailCol` AS email" : "'info@dialogoydesarrollo.com.pe' AS email",
        $telCol ? "`$telCol` AS telefono" : "'' AS telefono",
        $dirCol ? "`$dirCol` AS direccion" : "'' AS direccion",
    ];

    $sql = "SELECT " . implode(", ", $select) . " FROM sobre_dyd";
    if ($estadoCol) $sql .= " WHERE `$estadoCol` = 'publicado'";
    if ($idCol) $sql .= " ORDER BY `$idCol` DESC";
    $sql .= " LIMIT 1";

    $sobre = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
}

if (!$sobre) {
    $sobre = [
        'titulo' => 'Diálogo y Desarrollo Perú',
        'subtitulo' => 'Periodismo independiente con una mirada constructiva',
        'contenido' => 'Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.',
        'mision' => '',
        'vision' => '',
        'imagen' => '',
        'email' => 'info@dialogoydesarrollo.com.pe',
        'telefono' => '',
        'direccion' => ''
    ];
}

$imagen = 'assets/images/bannerimg.jpg';

if (!empty($sobre['imagen'])) {
    $valor = trim((string)$sobre['imagen']);
    $nombre = basename($valor);

    $candidatos = [
        [__DIR__ . '/admin/uploads/sobre/' . $nombre, 'admin/uploads/sobre/' . rawurlencode($nombre)],
        [__DIR__ . '/uploads/sobre/' . $nombre, 'uploads/sobre/' . rawurlencode($nombre)],
        [__DIR__ . '/assets/images/' . $nombre, 'assets/images/' . rawurlencode($nombre)],
    ];

    foreach ($candidatos as [$fisica, $publica]) {
        if (is_file($fisica)) {
            $imagen = $publica;
            break;
        }
    }

    if (preg_match('~^https?://~i', $valor)) {
        $imagen = $valor;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sobre D&D - Diálogo y Desarrollo Perú</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <style>
      .sobre-img{width:100%;height:420px;object-fit:cover;border-radius:16px;box-shadow:0 8px 28px rgba(0,0,0,.10)}
      .sobre-texto p{font-size:17px;line-height:1.85;color:#555}
      .sobre-box{background:#fff;border-radius:14px;padding:28px;height:100%;box-shadow:0 5px 20px rgba(0,0,0,.07)}
      .sobre-contacto{background:#f7f8fb;border-radius:14px;padding:28px}
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
          <li class="nav-item"><a class="nav-link" href="alianzas.php">Alianzas</a></li>
          <li class="nav-item active"><a class="nav-link" href="sobre-dd.php">Sobre D&D</a></li>
          <li class="ml-2"><a href="contacto.php" class="btn btn-style btn-outline-secondary">Contacto</a></li>
        </ul>
      </div>
    </nav>
  </div>
</header>

<section class="breadcrumb-area py-sm-5 py-4">
  <div class="container">
    <div class="breadcrumb-contents">
      <h2 class="title-big">Sobre D&D</h2>
      <?php if (!empty($sobre['subtitulo'])): ?>
        <p class="mt-2"><?= htmlspecialchars((string)$sobre['subtitulo'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container py-lg-4">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <img src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>"
             onerror="this.onerror=null;this.src='assets/images/bannerimg.jpg';"
             class="sobre-img" alt="Diálogo y Desarrollo Perú">
      </div>

      <div class="col-lg-6">
        <h5 class="title-small mb-2">Quiénes Somos</h5>
        <h3 class="title-big mb-4"><?= htmlspecialchars((string)$sobre['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="sobre-texto">
          <p><?= nl2br(htmlspecialchars((string)$sobre['contenido'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($sobre['mision']) || !empty($sobre['vision'])): ?>
<section class="py-5" style="background:#f7f8fb;">
  <div class="container">
    <div class="row">
      <?php if (!empty($sobre['mision'])): ?>
      <div class="col-md-6 mb-4">
        <div class="sobre-box">
          <h4>Misión</h4>
          <p><?= nl2br(htmlspecialchars((string)$sobre['mision'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($sobre['vision'])): ?>
      <div class="col-md-6 mb-4">
        <div class="sobre-box">
          <h4>Visión</h4>
          <p><?= nl2br(htmlspecialchars((string)$sobre['vision'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-5">
  <div class="container">
    <div class="sobre-contacto">
      <h3 class="title-big mb-4">Contacto</h3>

      <?php if (!empty($sobre['email'])): ?>
        <p><strong>Correo:</strong> <?= htmlspecialchars((string)$sobre['email'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if (!empty($sobre['telefono'])): ?>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars((string)$sobre['telefono'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if (!empty($sobre['direccion'])): ?>
        <p><strong>Dirección:</strong> <?= htmlspecialchars((string)$sobre['direccion'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<script src="assets/js/jquery-3.3.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
