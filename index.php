<?php
require_once 'nav.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>RadioTools</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <?php renderNavMenu('index.php'); ?>

        <div class="header" style="text-align: center; margin: 30px 0;">
            <h1 style="font-size: 24px; color: #4CAF50; margin-bottom: 10px;">
                🖖🏼 Bienvenido/a a RadioTools
            </h1>
            <p style="color: #888; font-size: 14px; line-height: 1.6; max-width: 500px; margin: 0 auto;">
                Una colección de herramientas web para radioaficionados: monitorización solar en tiempo real,
                calculadora de propagación HF, visualizador del espectro radioeléctrico y gestor de QSOs.
                Diseñado para facilitar la planificación de contactos DX y optimizar tus comunicaciones.
            </p>
        </div>

        <div style="text-align: center; margin-top: 20px; padding-bottom: 20px;">
            <p style="color: #666; font-size: 13px;">📡 Usa el menú de arriba para navegar entre herramientas</p>
            <p style="color: #4CAF50; font-size: 14px; margin-top: 10px;">73! - Buena propagación y buenos contactos DX</p>
        </div>
    </div>
</body>
</html>