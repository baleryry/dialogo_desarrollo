<?php

function e(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

function redirect(string $ruta): never
{
    header('Location: ' . APP_URL . $ruta);
    exit;
}

function adminRedirect(string $archivo = 'index.php'): never
{
    header('Location: ' . ADMIN_URL . '/' . ltrim($archivo, '/'));
    exit;
}

function fechaLatina(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }

    $t = strtotime($fecha);

    return $t ? date('d/m/Y', $t) : $fecha;
}

function rolNormalizado(?string $rol): string
{
    return strtolower(trim((string)$rol));
}

function esAdministrador(?string $rol): bool
{
    return in_array(
        rolNormalizado($rol),
        ['admin', 'administrador'],
        true
    );
}

function subirArchivo(
    string $campo,
    string $carpeta,
    array $extPermitidas,
    int $maxBytes = 8388608
): ?string {
    if (
        empty($_FILES[$campo]) ||
        ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    $f = $_FILES[$campo];

    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir el archivo.');
    }

    if (($f['size'] ?? 0) > $maxBytes) {
        throw new RuntimeException('El archivo excede el tamaño permitido.');
    }

    $ext = strtolower(
        pathinfo(
            (string)($f['name'] ?? ''),
            PATHINFO_EXTENSION
        )
    );

    if (!in_array($ext, $extPermitidas, true)) {
        throw new RuntimeException('Tipo de archivo no permitido.');
    }

    $nombre = bin2hex(random_bytes(10)) . '.' . $ext;

    $dir = dirname(__DIR__) . '/uploads/' . $carpeta;

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $destino = $dir . '/' . $nombre;

    if (!move_uploaded_file($f['tmp_name'], $destino)) {
        throw new RuntimeException('No se pudo guardar el archivo.');
    }

    return $nombre;
}

function subirImagenesMultiples(
    string $campo,
    string $carpeta,
    int $maxBytes = 8388608
): array {
    if (
        empty($_FILES[$campo]) ||
        !isset($_FILES[$campo]['name']) ||
        !is_array($_FILES[$campo]['name'])
    ) {
        return [];
    }

    $guardados = [];

    foreach ($_FILES[$campo]['name'] as $i => $original) {
        $error = $_FILES[$campo]['error'][$i] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir una foto adicional.');
        }

        if (
            ($_FILES[$campo]['size'][$i] ?? 0) >
            $maxBytes
        ) {
            throw new RuntimeException(
                'Una foto adicional excede el tamaño permitido.'
            );
        }

        $ext = strtolower(
            pathinfo(
                (string)$original,
                PATHINFO_EXTENSION
            )
        );

        if (
            !in_array(
                $ext,
                ['jpg', 'jpeg', 'png', 'webp'],
                true
            )
        ) {
            throw new RuntimeException(
                'Formato de foto adicional no permitido.'
            );
        }

        $nombre = bin2hex(random_bytes(10)) . '.' . $ext;

        $dir = dirname(__DIR__) . '/uploads/' . $carpeta;

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (
            !move_uploaded_file(
                $_FILES[$campo]['tmp_name'][$i],
                $dir . '/' . $nombre
            )
        ) {
            throw new RuntimeException(
                'No se pudo guardar una foto adicional.'
            );
        }

        $guardados[] = $nombre;
    }

    return $guardados;
}

function eliminarArchivoSubido(
    string $carpeta,
    ?string $nombre
): void {
    if (!$nombre) {
        return;
    }

    $ruta = dirname(__DIR__) .
            '/uploads/' .
            $carpeta .
            '/' .
            basename($nombre);

    if (is_file($ruta)) {
        @unlink($ruta);
    }
}

function youtubeEmbed(string $url): string
{
    if (
        preg_match(
            '~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~',
            $url,
            $m
        )
    ) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    return $url;
}
