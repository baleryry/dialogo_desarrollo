<?php

require_once __DIR__ . '/includes/admin_layout.php';

exigirLogin();

$pdo = db();

function contarTabla(
    PDO $pdo,
    string $tabla
): int {
    $permitidas = [
        'reportajes',
        'noticias',
        'boletines',
        'podcasts',
        'videos',
        'usuarios'
    ];

    if (!in_array($tabla, $permitidas, true)) {
        return 0;
    }

    try {
        return (int)$pdo
            ->query(
                "SELECT COUNT(*) FROM `$tabla`"
            )
            ->fetchColumn();

    } catch (Throwable $e) {
        return 0;
    }
}

$cards = [
    [
        'Reportajes',
        'reportajes',
        'bi-newspaper',
        'reportajes.php'
    ],
    [
        'Noticias',
        'noticias',
        'bi-megaphone',
        'noticias.php'
    ],
    [
        'Boletines',
        'boletines',
        'bi-file-earmark-pdf',
        'boletines.php'
    ],
    [
        'Podcasts',
        'podcasts',
        'bi-mic',
        'podcasts.php'
    ],
    [
        'Videos',
        'videos',
        'bi-play-btn',
        'videos.php'
    ],
];

adminHeader('Dashboard');
?>

<div class="row g-4">

<?php foreach ($cards as [$titulo, $tabla, $icono, $url]): ?>

    <div class="col-lg-3 col-6">

        <div class="small-box text-bg-light">

            <div class="inner">

                <h3>
                    <?= contarTabla($pdo, $tabla) ?>
                </h3>

                <p>
                    <?= e($titulo) ?>
                </p>

            </div>

            <i
                class="
                    small-box-icon
                    bi
                    <?= e($icono) ?>
                "
            ></i>

            <a
                href="<?= ADMIN_URL ?>/<?= e($url) ?>"
                class="
                    small-box-footer
                    link-dark
                    link-underline-opacity-0
                "
            >
                Gestionar
                <i class="bi bi-arrow-right-circle"></i>
            </a>

        </div>

    </div>

<?php endforeach; ?>

</div>


<div class="card shadow-sm mt-4">

    <div
        class="
            card-body
            d-flex
            justify-content-between
            align-items-center
            flex-wrap
            gap-3
        "
    >

        <div>

           
           

        </div>

        <a
            href="<?= APP_URL ?>/index.php"
            target="_blank"
            class="btn btn-danger"
        >
            <i
                class="
                    bi
                    bi-box-arrow-up-right
                    me-1
                "
            ></i>
            Ver sitio público
        </a>

    </div>

</div>

<?php adminFooter(); ?>
