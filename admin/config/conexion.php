<?php

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST .
           ';dbname=' . DB_NAME .
           ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $pdo;

    } catch (PDOException $e) {
        http_response_code(500);

        exit(
            'No se pudo conectar con MySQL. ' .
            'Revisa admin/config/config.php. Detalle: ' .
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
        );
    }
}
