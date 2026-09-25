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
    header(
        'Location: ' .
        ADMIN_URL .
        '/' .
        ltrim($archivo, '/')
    );

    exit;
}


function fechaLatina(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }

    $t = strtotime($fecha);

    return $t
        ? date('d/m/Y', $t)
        : $fecha;
}


function rolNormalizado(?string $rol): string
{
    return strtolower(
        trim((string)$rol)
    );
}


function esAdministrador(?string $rol): bool
{
    return in_array(
        rolNormalizado($rol),
        [
            'admin',
            'administrador'
        ],
        true
    );
}


/* ============================================================
   SUBIR UN ARCHIVO
   - Valida errores de PHP
   - Valida tamaño
   - Valida extensión
   - Valida tipo MIME real
   - Genera nombre seguro
   ============================================================ */

function subirArchivo(
    string $campo,
    string $carpeta,
    array $extPermitidas,
    int $maxBytes = 8388608
): ?string {

    /*
     * Si no se seleccionó ningún archivo,
     * devolvemos null normalmente.
     */
    if (
        empty($_FILES[$campo]) ||
        (
            $_FILES[$campo]['error']
            ?? UPLOAD_ERR_NO_FILE
        ) === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }


    $f = $_FILES[$campo];

    $errorSubida =
        (int)(
            $f['error']
            ?? UPLOAD_ERR_NO_FILE
        );


    /*
     * Convertir límite a MB
     * para mostrar un mensaje entendible.
     */
    $maxMB =
        round(
            $maxBytes / 1024 / 1024,
            2
        );


    /* ========================================================
       ERRORES DE SUBIDA PHP
       ======================================================== */

    if ($errorSubida !== UPLOAD_ERR_OK) {

        switch ($errorSubida) {

            case UPLOAD_ERR_INI_SIZE:

                throw new RuntimeException(
                    'El archivo supera el tamaño máximo permitido por el servidor.'
                );


            case UPLOAD_ERR_FORM_SIZE:

                throw new RuntimeException(
                    'El archivo supera el tamaño máximo permitido por el formulario.'
                );


            case UPLOAD_ERR_PARTIAL:

                throw new RuntimeException(
                    'El archivo se subió de manera incompleta.'
                );


            case UPLOAD_ERR_NO_FILE:

                return null;


            case UPLOAD_ERR_NO_TMP_DIR:

                throw new RuntimeException(
                    'El servidor no tiene una carpeta temporal disponible.'
                );


            case UPLOAD_ERR_CANT_WRITE:

                throw new RuntimeException(
                    'El servidor no pudo escribir el archivo.'
                );


            case UPLOAD_ERR_EXTENSION:

                throw new RuntimeException(
                    'Una extensión de PHP detuvo la subida del archivo.'
                );


            default:

                throw new RuntimeException(
                    'No se pudo subir el archivo. Código de error: ' .
                    $errorSubida
                );
        }
    }


    /* ========================================================
       VALIDAR TAMAÑO
       ======================================================== */

    $tamano =
        (int)(
            $f['size']
            ?? 0
        );


    if ($tamano <= 0) {
        throw new RuntimeException(
            'El archivo está vacío o no pudo ser leído.'
        );
    }


    if ($tamano > $maxBytes) {
        throw new RuntimeException(
            'El archivo no puede superar los ' .
            $maxMB .
            ' MB.'
        );
    }


    /* ========================================================
       VALIDAR EXTENSIÓN
       ======================================================== */

    $ext =
        strtolower(
            pathinfo(
                (string)(
                    $f['name']
                    ?? ''
                ),
                PATHINFO_EXTENSION
            )
        );


    /*
     * Normalizar extensiones permitidas
     * por seguridad.
     */
    $extPermitidas =
        array_map(
            'strtolower',
            $extPermitidas
        );


    if (
        $ext === '' ||
        !in_array(
            $ext,
            $extPermitidas,
            true
        )
    ) {
        throw new RuntimeException(
            'Tipo de archivo no permitido. Permitidos: ' .
            strtoupper(
                implode(
                    ', ',
                    $extPermitidas
                )
            ) .
            '.'
        );
    }


    /* ========================================================
       VALIDAR TIPO REAL DEL ARCHIVO
       ======================================================== */

    $tiposMime = [

        'jpg' => [
            'image/jpeg'
        ],

        'jpeg' => [
            'image/jpeg'
        ],

        'png' => [
            'image/png'
        ],

        'webp' => [
            'image/webp'
        ],

        'pdf' => [
            'application/pdf'
        ]

    ];


    if (
        function_exists('finfo_open') &&
        isset($tiposMime[$ext])
    ) {

        $finfo =
            finfo_open(
                FILEINFO_MIME_TYPE
            );


        if ($finfo !== false) {

            $mime =
                finfo_file(
                    $finfo,
                    $f['tmp_name']
                );

            finfo_close($finfo);


            if (
                $mime === false ||
                !in_array(
                    $mime,
                    $tiposMime[$ext],
                    true
                )
            ) {
                throw new RuntimeException(
                    'El contenido del archivo no corresponde al formato ' .
                    strtoupper($ext) .
                    '.'
                );
            }
        }
    }


    /* ========================================================
       GENERAR NOMBRE SEGURO
       ======================================================== */

    $nombre =
        bin2hex(
            random_bytes(10)
        ) .
        '.' .
        $ext;


    /* ========================================================
       CARPETA DE DESTINO
       ======================================================== */

    $dir =
        dirname(__DIR__) .
        '/uploads/' .
        trim(
            $carpeta,
            '/\\'
        );


    if (!is_dir($dir)) {

        if (
            !mkdir(
                $dir,
                0775,
                true
            ) &&
            !is_dir($dir)
        ) {
            throw new RuntimeException(
                'No se pudo crear la carpeta de archivos.'
            );
        }
    }


    $destino =
        $dir .
        '/' .
        $nombre;


    /* ========================================================
       GUARDAR ARCHIVO
       ======================================================== */

    if (
        !move_uploaded_file(
            $f['tmp_name'],
            $destino
        )
    ) {
        throw new RuntimeException(
            'No se pudo guardar el archivo en el servidor.'
        );
    }


    return $nombre;
}


/* ============================================================
   SUBIR VARIAS IMÁGENES
   - JPG
   - JPEG
   - PNG
   - WEBP
   - Tamaño máximo individual
   ============================================================ */

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


    $maxMB =
        round(
            $maxBytes / 1024 / 1024,
            2
        );


    $extPermitidas = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];


    $tiposMime = [

        'jpg' => [
            'image/jpeg'
        ],

        'jpeg' => [
            'image/jpeg'
        ],

        'png' => [
            'image/png'
        ],

        'webp' => [
            'image/webp'
        ]

    ];


    foreach (
        $_FILES[$campo]['name']
        as $i => $original
    ) {

        $error =
            (int)(
                $_FILES[$campo]['error'][$i]
                ?? UPLOAD_ERR_NO_FILE
            );


        /*
         * Campo vacío.
         */
        if (
            $error ===
            UPLOAD_ERR_NO_FILE
        ) {
            continue;
        }


        /* ====================================================
           ERRORES PHP
           ==================================================== */

        if ($error !== UPLOAD_ERR_OK) {

            switch ($error) {

                case UPLOAD_ERR_INI_SIZE:

                    throw new RuntimeException(
                        'Una foto adicional supera el tamaño máximo permitido por el servidor.'
                    );


                case UPLOAD_ERR_FORM_SIZE:

                    throw new RuntimeException(
                        'Una foto adicional supera el tamaño máximo permitido.'
                    );


                case UPLOAD_ERR_PARTIAL:

                    throw new RuntimeException(
                        'Una foto adicional se subió de manera incompleta.'
                    );


                case UPLOAD_ERR_NO_TMP_DIR:

                    throw new RuntimeException(
                        'El servidor no tiene carpeta temporal disponible.'
                    );


                case UPLOAD_ERR_CANT_WRITE:

                    throw new RuntimeException(
                        'El servidor no pudo escribir una de las fotos.'
                    );


                default:

                    throw new RuntimeException(
                        'Error al subir una foto adicional. Código: ' .
                        $error
                    );
            }
        }


        /* ====================================================
           VALIDAR TAMAÑO
           ==================================================== */

        $tamano =
            (int)(
                $_FILES[$campo]['size'][$i]
                ?? 0
            );


        if ($tamano <= 0) {
            throw new RuntimeException(
                'Una foto adicional está vacía o no pudo ser leída.'
            );
        }


        if ($tamano > $maxBytes) {
            throw new RuntimeException(
                'Cada foto adicional debe pesar como máximo ' .
                $maxMB .
                ' MB.'
            );
        }


        /* ====================================================
           VALIDAR EXTENSIÓN
           ==================================================== */

        $ext =
            strtolower(
                pathinfo(
                    (string)$original,
                    PATHINFO_EXTENSION
                )
            );


        if (
            !in_array(
                $ext,
                $extPermitidas,
                true
            )
        ) {
            throw new RuntimeException(
                'Formato de foto adicional no permitido. ' .
                'Solo JPG, JPEG, PNG o WEBP.'
            );
        }


        /* ====================================================
           ARCHIVO TEMPORAL
           ==================================================== */

        $tmp =
            $_FILES[$campo]['tmp_name'][$i]
            ?? '';


        if (
            $tmp === '' ||
            !is_uploaded_file($tmp)
        ) {
            throw new RuntimeException(
                'No se pudo validar una de las fotos adicionales.'
            );
        }


        /* ====================================================
           VALIDAR MIME REAL
           ==================================================== */

        if (
            function_exists('finfo_open') &&
            isset($tiposMime[$ext])
        ) {

            $finfo =
                finfo_open(
                    FILEINFO_MIME_TYPE
                );


            if ($finfo !== false) {

                $mime =
                    finfo_file(
                        $finfo,
                        $tmp
                    );

                finfo_close($finfo);


                if (
                    $mime === false ||
                    !in_array(
                        $mime,
                        $tiposMime[$ext],
                        true
                    )
                ) {
                    throw new RuntimeException(
                        'Una foto adicional no corresponde realmente al formato ' .
                        strtoupper($ext) .
                        '.'
                    );
                }
            }
        }


        /* ====================================================
           CREAR NOMBRE
           ==================================================== */

        $nombre =
            bin2hex(
                random_bytes(10)
            ) .
            '.' .
            $ext;


        /* ====================================================
           CARPETA
           ==================================================== */

        $dir =
            dirname(__DIR__) .
            '/uploads/' .
            trim(
                $carpeta,
                '/\\'
            );


        if (!is_dir($dir)) {

            if (
                !mkdir(
                    $dir,
                    0775,
                    true
                ) &&
                !is_dir($dir)
            ) {
                throw new RuntimeException(
                    'No se pudo crear la carpeta para las fotos.'
                );
            }
        }


        /* ====================================================
           GUARDAR
           ==================================================== */

        if (
            !move_uploaded_file(
                $tmp,
                $dir . '/' . $nombre
            )
        ) {
            throw new RuntimeException(
                'No se pudo guardar una foto adicional.'
            );
        }


        $guardados[] =
            $nombre;
    }


    return $guardados;
}


/* ============================================================
   ELIMINAR ARCHIVO SUBIDO
   ============================================================ */

function eliminarArchivoSubido(
    string $carpeta,
    ?string $nombre
): void {

    if (!$nombre) {
        return;
    }


    $ruta =
        dirname(__DIR__) .
        '/uploads/' .
        trim(
            $carpeta,
            '/\\'
        ) .
        '/' .
        basename($nombre);


    if (is_file($ruta)) {
        @unlink($ruta);
    }
}


/* ============================================================
   CONVERTIR URL YOUTUBE A EMBED
   ============================================================ */

function youtubeEmbed(string $url): string
{
    if (
        preg_match(
            '~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~',
            $url,
            $m
        )
    ) {
        return 'https://www.youtube.com/embed/' .
               $m[1];
    }


    return $url;
}