<?php
require_once 'nav.php';

$links_config = [
    [
        'title' => '👤 Comunidad Ham',
        'links' => [
            ['icon' => '📇', 'text' => 'QRZ.com', 'url' => 'https://www.qrz.com/', 'desc' => 'Base de datos de radioaficionados'],
            ['icon' => '🔁', 'text' => 'RepeaterBook', 'url' => 'https://www.repeaterbook.com/row_repeaters/prox2_result.php?city=in83BJ&lat=&long=&distance=25&Dunit=k&band%5B%5D=1&band%5B%5D=2&band%5B%5D=4096&band%5B%5D=4&band%5B%5D=16&freq=&call=&mode%5B%5D=1&status_id=%25', 'desc' => 'Repetidores cercanos'],
        ]
    ],
    [
        'title' => '🗺️ Mapas y Ubicación',
        'links' => [
            ['icon' => '🏔️', 'text' => 'Mapa Topográfico', 'url' => 'https://es-es.topographic-map.com/map-3fzz4/Cantabria/?center=43.38259%2C-3.87405&zoom=11', 'desc' => 'Topografía de Cantabria'],
            ['icon' => '📍', 'text' => 'SOTL.as', 'url' => 'https://sotl.as/mapa/', 'desc' => 'Mapa de estaciones de radioaficionados'],
        ]
    ],
    [
        'title' => '📻 SDR y Frecuencias',
        'links' => [
            ['icon' => '📡', 'text' => 'websdr.org', 'url' => 'https://websdr.org/', 'desc' => 'Software para receptores SDR'],
            ['icon' => '📡', 'text' => 'SDR Madrid', 'url' => 'http://rem-esp.spdns.org:8901/', 'desc' => 'Receptor SDR online'],
            ['icon' => '📡', 'text' => 'SDR Cercedilla', 'url' => 'http://laradiocb.ddns.me:8073/#freq=7180500,mod=lsb,sql=-150', 'desc' => 'Receptor SDR online'],
            ['icon' => '📻', 'text' => 'Frecuencias en España', 'url' => 'https://sdrmadrid.com/sdr-frecuencias-en-espana-una-guia-completa-para-radioescuchas-y-radioaficionados/', 'desc' => 'Guía completa de frecuencias'],
        ]
    ],
    [
        'title' => '🛰️ Satélites y Sondas',
        'links' => [
            ['icon' => '🛰️', 'text' => 'Satélites Ham', 'url' => 'https://heavens-above.com/AmateurSats.aspx?lat=43.4165&lng=-3.8468&loc=Unnamed&alt=0&tz=CET', 'desc' => 'Satélites de radioaficionados'],
            ['icon' => '🚀', 'text' => 'ISS Tracker', 'url' => 'http://www.isstracker.com/', 'desc' => 'Seguimiento de la ISS en tiempo real'],
            ['icon' => '🎈', 'text' => 'SondeHub', 'url' => 'https://sondehub.org', 'desc' => 'Rastreo de radiosondas meteorológicas - ¡Geocaching radiofónico!'],
        ]
    ],
    [
        'title' => '🛒 Tiendas',
        'links' => [
            ['icon' => '🛒', 'text' => 'AstroRadio', 'url' => 'https://www.astroradio.com', 'desc' => 'Tienda de equipos de radioaficionado'],
            ['icon' => '🛒', 'text' => 'PNI.es', 'url' => 'https://www.pni.es/', 'desc' => 'Equipos de comunicación'],
        ]
    ],
    [
        'title' => '🔧 Proyectos DIY',
        'links' => [
            ['icon' => '🔧', 'text' => 'DIY Radio', 'url' => 'https://docs.google.com/document/d/1r2CloQgovSGbe9PjPuUlWYmzm1d-khQ1XCfvc8nwFkI/edit?usp=sharing', 'desc' => 'Proyectos y construcciones DIY'],
        ]
    ],
    [
        'title' => '🕐 Utilidades',
        'links' => [
            ['icon' => '🕐', 'text' => 'Time & Date', 'url' => 'https://www.timeanddate.com/', 'desc' => 'Hora mundial y zonas horarias'],
            ['icon' => '📟', 'text' => 'Morsle', 'url' => 'https://morsle.fun/', 'desc' => 'Juego de práctica de código morse'],
        ]
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Enlaces de Interés - RadioTools</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .links-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .links-section {
            margin-bottom: 20px;
        }

        .section-header {
            font-size: 16px;
            color: #4CAF50;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #333;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }

        .link-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .link-card:hover {
            border-color: #4CAF50;
            background: #222;
            transform: translateY(-2px);
        }

        .link-header {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .link-icon {
            font-size: 20px;
        }

        .link-text {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        .link-desc {
            font-size: 12px;
            color: #888;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="container links-container">
        <?php renderNavMenu('links.php'); ?>

        <?php foreach ($links_config as $section): ?>
        <div class="links-section">
            <div class="section-header"><?php echo $section['title']; ?></div>
            <div class="links-grid">
                <?php foreach ($section['links'] as $link): ?>
                    <a href="<?php echo htmlspecialchars($link['url']); ?>"
                       target="_blank"
                       class="link-card">
                        <div class="link-header">
                            <span class="link-icon"><?php echo $link['icon']; ?></span>
                            <span class="link-text"><?php echo htmlspecialchars($link['text']); ?></span>
                        </div>
                        <div class="link-desc"><?php echo htmlspecialchars($link['desc']); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
