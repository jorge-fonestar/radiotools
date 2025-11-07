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
        
        <div class="header">
            <h1>🖖🏼 Bienvenido/a a mi kit de herramientas de radioafición</h1>
            <div class="update-time">
                Una suite completa de links y herramientas que considero de utilidad.
            </div>
        </div>

        <div class="links-section">
            <div class="links-title">🔗 Enlaces de Interés</div>
            <div>
                <a href="https://es-es.topographic-map.com/map-3fzz4/Cantabria/?center=43.38259%2C-3.87405&zoom=11" 
                   target="_blank" class="link-item">
                    🗺️ Mapa topográfico
                </a>
                <a href="https://sdrmadrid.com/sdr-frecuencias-en-espana-una-guia-completa-para-radioescuchas-y-radioaficionados/" 
                   target="_blank" class="link-item">
                    📻 Frecuencias en España
                </a>
                <a href="https://www.rtl-sdr.com/" 
                   target="_blank" class="link-item">
                    📡 RTL-SDR.com
                </a>
                <a href="https://heavens-above.com/AmateurSats.aspx?lat=43.4165&lng=-3.8468&loc=Unnamed&alt=0&tz=CET" 
                   target="_blank" class="link-item">
                    🛰️ Satélites de radioaficionados
                </a>
                <a href="http://www.isstracker.com/" 
                   target="_blank" class="link-item">
                    🚀 ISS Tracker
                </a>
                <a href="https://www.astroradio.com" 
                   target="_blank" class="link-item">
                    🛒 Tienda AstroRadio.com
                </a>
                <a href="https://www.pni.es/" 
                   target="_blank" class="link-item">
                    🛒 Tienda PNI.es
                </a>
            </div>
        </div>

        <div class="features-grid">
            <a href="solar.php" class="feature-card">
                <div class="feature-icon">📡</div>
                <div class="feature-title">Monitor Solar</div>
                <div class="feature-description">
                    Monitorea en tiempo real las condiciones solares: SFI, índices K/A, 
                    manchas solares, rayos X y condiciones por banda HF y VHF.
                </div>
            </a>

            <a href="propagacion.php" class="feature-card">
                <div class="feature-icon">📻</div>
                <div class="feature-title">Calculador de Propagación</div>
                <div class="feature-description">
                    Calcula las mejores rutas de propagación DX desde tu QTH hacia diferentes 
                    regiones del mundo para cada banda HF.
                </div>
            </a>

            <a href="espectro-visual.html" class="feature-card">
                <div class="feature-icon">🌈</div>
                <div class="feature-title">Espectro Visual</div>
                <div class="feature-description">
                    Visualizador interactivo del espectro radioeléctrico con información 
                    detallada de bandas y asignaciones de frecuencias.
                </div>
            </a>
        </div>
        

        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 12px;">
            <p>🔧 Desarrollado para la comunidad de radioaficionados</p>
            <p>📡 73! - Buena propagación y buenos contactos DX</p>
        </div>
    </div>

    <script>
        // Mostrar estado de conectividad
        window.addEventListener('online', function() {
            console.log('🌐 Conectado - Datos en tiempo real disponibles');
        });
        
        window.addEventListener('offline', function() {
            console.log('📡 Usando datos en cache');
        });
    </script>
</body>
</html>