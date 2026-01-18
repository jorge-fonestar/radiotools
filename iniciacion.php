<?php
require_once 'nav.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎙️ Iniciación - RadioTools</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .wide-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .guide-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            border-radius: 12px;
            padding: 30px 25px;
            border: 2px solid #4CAF50;
            margin-bottom: 30px;
            text-align: center;
        }

        .guide-header h1 {
            font-size: 26px;
            color: #4CAF50;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .guide-header p {
            font-size: 14px;
            color: #aaa;
            line-height: 1.5;
        }

        .block-section {
            background: #1a1a1a;
            border-radius: 12px;
            border: 1px solid #333;
            margin-bottom: 25px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .block-section:hover {
            border-color: #4CAF50;
            background: #222;
        }

        .block-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
        }

        .block-title {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .block-number {
            background: #4CAF50;
            color: #0a0a0a;
            font-size: 14px;
            font-weight: bold;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .block-title h2 {
            font-size: 18px;
            color: #4CAF50;
            margin: 0;
            font-weight: 600;
        }

        .block-subtitle {
            font-size: 13px;
            color: #888;
            margin: 0;
            font-style: italic;
        }

        .block-expand-arrow {
            font-size: 14px;
            color: #666;
            transition: transform 0.3s ease;
        }

        .block-section.expanded .block-expand-arrow {
            transform: rotate(180deg);
        }

        .block-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }

        .block-section.expanded .block-content {
            max-height: 2000px;
        }

        .block-content-inner {
            padding: 25px;
            border-top: 1px solid #333;
            background: #151515;
        }

        .block-intro {
            font-size: 14px;
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(76, 175, 80, 0.05);
            border-left: 3px solid #4CAF50;
            border-radius: 4px;
        }

        .mission-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .mission-item {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            border-left: 3px solid #FFC107;
            transition: all 0.2s ease;
        }

        .mission-item:hover {
            background: #222;
            border-color: #FFC107;
        }

        .mission-label {
            font-size: 13px;
            font-weight: 600;
            color: #FFC107;
            margin-bottom: 8px;
            display: block;
        }

        .mission-text {
            font-size: 13px;
            color: #ccc;
            line-height: 1.5;
        }

        .key-concept {
            background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
            border: 1px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .key-concept-label {
            font-size: 12px;
            font-weight: bold;
            color: #4CAF50;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .key-concept-text {
            font-size: 13px;
            color: #ddd;
            line-height: 1.5;
        }

        .experiment-box {
            background: #1a1a1a;
            border: 2px solid #FF9800;
            border-radius: 10px;
            padding: 18px;
            margin: 15px 0;
        }

        .experiment-title {
            font-size: 14px;
            font-weight: bold;
            color: #FF9800;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .experiment-steps {
            font-size: 13px;
            color: #ccc;
            line-height: 1.6;
        }

        .result-box {
            background: linear-gradient(135deg, #1a4d1a 0%, #0d260d 100%);
            border: 1px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            margin-top: 12px;
        }

        .result-label {
            font-size: 12px;
            font-weight: bold;
            color: #4CAF50;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .result-text {
            font-size: 13px;
            color: #a5d6a7;
            line-height: 1.5;
        }

        .hardware-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .hardware-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 18px;
            transition: all 0.3s ease;
            border-left: 4px solid #2196F3;
        }

        .hardware-card:hover {
            background: #222;
            border-color: #2196F3;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
        }

        .hardware-name {
            font-size: 16px;
            font-weight: bold;
            color: #2196F3;
            margin-bottom: 8px;
        }

        .hardware-price {
            font-size: 13px;
            color: #FFC107;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .hardware-desc {
            font-size: 13px;
            color: #aaa;
            line-height: 1.5;
        }

        .hardware-image {
            width: 100%;
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid #333;
        }

        .license-steps {
            list-style: none;
            padding: 0;
            margin: 15px 0;
            counter-reset: step-counter;
        }

        .license-step {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            position: relative;
            padding-left: 55px;
        }

        .license-step::before {
            counter-increment: step-counter;
            content: counter(step-counter);
            position: absolute;
            left: 15px;
            top: 15px;
            background: #673AB7;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .step-title {
            font-size: 14px;
            font-weight: bold;
            color: #673AB7;
            margin-bottom: 8px;
        }

        .step-desc {
            font-size: 13px;
            color: #ccc;
            line-height: 1.5;
        }

        .step-list {
            margin-top: 10px;
            padding-left: 20px;
        }

        .step-list li {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 5px;
        }

        .extras-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }

        .extra-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
            border: 1px solid #333;
            border-radius: 10px;
            padding: 16px;
            transition: all 0.3s ease;
        }

        .extra-card:hover {
            background: linear-gradient(135deg, #252525 0%, #2a2a2a 100%);
            border-color: #E91E63;
            transform: scale(1.03);
        }

        .extra-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .extra-title {
            font-size: 14px;
            font-weight: bold;
            color: #E91E63;
            margin-bottom: 8px;
        }

        .extra-desc {
            font-size: 12px;
            color: #aaa;
            line-height: 1.4;
        }

        .highlight {
            color: #4CAF50;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .guide-header h1 {
                font-size: 22px;
            }

            .hardware-grid,
            .extras-grid {
                grid-template-columns: 1fr;
            }

            .block-title h2 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php renderNavMenu('iniciacion.php'); ?>

    <div class="wide-container">
        <div class="guide-header">
            <h1>🎙️ La Hoja de Ruta del Radio-Hacker</h1>
            <p>Una guía práctica para entrar al mundo de la radioafición sin miedo, sin burocracia inicial y con experimentos desde el primer día.</p>
        </div>

        <!-- BLOQUE 1 -->
        <div class="block-section" onclick="toggleBlock(this)">
            <div class="block-header">
                <div class="block-title">
                    <div class="block-number">1</div>
                    <div>
                        <h2>Primeros Pasos</h2>
                        <p class="block-subtitle">El "Voyeur" de las Ondas</p>
                    </div>
                </div>
                <span class="block-expand-arrow">▼</span>
            </div>
            <div class="block-content">
                <div class="block-content-inner">
                    <div class="block-intro">
                        El objetivo aquí es echar un vistazo a las ondas sin gastar un euro. Puedes escuchar emisoras de todo el mundo!
                    </div>

                    <h3 style="font-size: 15px; color: #4CAF50; margin-bottom: 12px;">📡 Sintoniza desde el navegador</h3>
                    <p style="font-size: 13px; color: #ccc; line-height: 1.6; margin-bottom: 15px;">
                        No necesitas hardware. Entra en <a href="http://websdr.org" target="_blank" style="color: #66bb6a; text-decoration: none;">WebSDR.org</a>.
                        Es una red mundial de receptores que puedes controlar con el ratón para escuchar en tiempo real.
                    </p>

                    <ul class="mission-list">
                        <li class="mission-item">
                            <span class="mission-label">Misión 1</span>
                            <p class="mission-text">Busca una antena en España (ej. la de la Universidad de Vigo o alguna en Madrid).</p>
                        </li>
                        <li class="mission-item">
                            <span class="mission-label">Misión 2</span>
                            <p class="mission-text">Ve a la banda de <strong>7.100 kHz (LSB)</strong> un domingo por la mañana. Escucharás a gente de toda España.
                            O sintoniza la Onda Corta de noche para oír emisoras de China, Rumanía o Brasil.</p>
                        </li>
                    </ul>

                </div>
            </div>
        </div>

        <!-- BLOQUE 2 -->
        <div class="block-section" onclick="toggleBlock(this)">
            <div class="block-header">
                <div class="block-title">
                    <div class="block-number">2</div>
                    <div>
                        <h2>Equipos Económicos y Potentes</h2>
                        <p class="block-subtitle">Hardware "Hackeable"</p>
                    </div>
                </div>
                <span class="block-expand-arrow">▼</span>
            </div>
            <div class="block-content">
                <div class="block-content-inner">
                    <div class="block-intro">
                        Para empezar no hace falta comprar los mejores equipos para poder <strong>empezar a "trastear"</strong>.
                    </div>

                    <div class="hardware-grid">
                        <div class="hardware-card">
                            <img src="img/quansheng-uv5k.png" alt="Quansheng UV-K5" class="hardware-image">
                            <div class="hardware-name">📻 Quansheng UV-K5</div>
                            <div class="hardware-price">~20€ (Aliexpress)</div>
                            <div class="hardware-desc">
                                El Rey del Low Cost. Este walkie es una locura. Se le puede cambiar el firmware
                                (el sistema operativo) para que reciba frecuencias de aviación, satélites y más.<br>
                                Con él podras ir a la montaña y estar siempre conectado, conectar con los repetidores de tu zona y
                                escuchar toda la actividad local.
                            </div>
                        </div>

                        <div class="hardware-card">
                            <img src="img/rtl-sdr.png" alt="RTL-SDR" class="hardware-image">
                            <div class="hardware-name">📡 RTL-SDR</div>
                            <div class="hardware-price">~40€</div>
                            <div class="hardware-desc">
                                El Pincho USB. Convierte tu PC en un escáner profesional. Verás el espectro radioeléctrico
                                en cascada de colores. Es como tener rayos X para ver qué hay en el aire.
                            </div>
                        </div>

                        <div class="hardware-card">
                            <img src="img/ats-mini.png" alt="ATS-Mini" class="hardware-image">
                            <div class="hardware-name">📻 ATS-Mini</div>
                            <div class="hardware-price">~25€</div>
                            <div class="hardware-desc">
                                Onda Corta. Un receptor dedicado para escuchar el mundo entero (CW, Modos digitales, SSB). Es muy pequeño y portátil, una gozada!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BLOQUE 3 -->
        <div class="block-section" onclick="toggleBlock(this)">
            <div class="block-header">
                <div class="block-title">
                    <div class="block-number">3</div>
                    <div>
                        <h2>Construcción de Antenas DIY</h2>
                        <p class="block-subtitle">El Arte del Cable</p>
                    </div>
                </div>
                <span class="block-expand-arrow">▼</span>
            </div>
            <div class="block-content">
                <div class="block-content-inner">
                    <div class="block-intro">
                        Si te gusta el "cacharreo" el siguiente paso es <strong>fabricar tus propias antenas</strong>.
                        El secreto no es el aparato, <strong>es la antena</strong>.
                    </div>

                    <h3 style="font-size: 15px; color: #FF9800; margin-bottom: 12px;">🔧 Material básico (por ~5€)</h3>
                    <p style="font-size: 13px; color: #ccc; line-height: 1.6; margin-bottom: 15px;">
                        Con muy poco dinero puedes comprar conectores BNC/SMA, reciclar cables largos y empezar a construir antenas
                        para tus equipos. Un rollo de cable de 1.5mm² cuesta unos pocos euros y te sirve para múltiples proyectos.
                    </p>

                    <div class="experiment-box">
                        <div class="experiment-title">
                            🧪 Tu Primera Antena: Long Wire
                        </div>
                        <div class="experiment-steps">
                            <ol style="padding-left: 20px; margin: 0;">
                                <li style="margin-bottom: 8px;">Consigue cable eléctrico de 1.5mm² (unos 10-20 metros)</li>
                                <li style="margin-bottom: 8px;">Pela un extremo y conéctalo al conector de antena de tu RTL-SDR o radio</li>
                                <li style="margin-bottom: 8px;">Tira el cable por la ventana, súbelo al tejado, o extiéndelo horizontal</li>
                                <li>¡Ya tienes una antena funcional para HF/VHF!</li>
                            </ol>
                        </div>

                        <div class="result-box">
                            <span class="result-label">✨ El resultado</span>
                            <p class="result-text">
                                Verás como el ruido de fondo desaparece y las señales lejanas "brotan" de la nada.
                                Con esta antena simple puedes recibir estaciones de onda corta de todo el mundo.
                            </p>
                        </div>
                    </div>

                    <h3 style="font-size: 15px; color: #FF9800; margin-bottom: 12px; margin-top: 20px;">🛠️ Proyectos DIY Avanzados</h3>
                    <p style="font-size: 13px; color: #ccc; line-height: 1.6; margin-bottom: 15px;">
                        Una vez domines lo básico, hay un mundo de proyectos esperándote:
                    </p>

                    <ul style="font-size: 13px; color: #ccc; line-height: 1.8; margin-bottom: 15px; padding-left: 25px;">
                        <li><strong>Dipolo para 40m/20m</strong>: La antena clásica de HF</li>
                        <li><strong>Antena Yagi</strong>: Para satélites y direccional</li>
                        <li><strong>Antena Slim Jim</strong>: Para VHF/UHF con un cable coaxial</li>
                        <li><strong>Loop magnético</strong>: Para espacios reducidos</li>
                        <li><strong>Balun 1:1 o 1:9</strong>: Para adaptar impedancias</li>
                    </ul>

                    <div class="key-concept">
                        <span class="key-concept-label">🔧 Más proyectos DIY</span>
                        <p class="key-concept-text">
                            Puedes consultar la sección <a href="https://docs.google.com/document/d/1r2CloQgovSGbe9PjPuUlWYmzm1d-khQ1XCfvc8nwFkI/edit?usp=sharing" target="_blank" style="color: #4CAF50; text-decoration: none; font-weight: bold;">🔧 Proyectos DIY</a>
                            para instrucciones detalladas, esquemas y ejemplos reales de construcción de antenas y equipos.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BLOQUE 4 -->
        <div class="block-section" onclick="toggleBlock(this)">
            <div class="block-header">
                <div class="block-title">
                    <div class="block-number">4</div>
                    <div>
                        <h2>Licencia e Indicativo</h2>
                        <p class="block-subtitle">El Paso al "Pro"</p>
                    </div>
                </div>
                <span class="block-expand-arrow">▼</span>
            </div>
            <div class="block-content">
                <div class="block-content-inner">
                    <div class="block-intro">
                        Quitar el miedo a la burocracia.
                    </div>

                    <h3 style="font-size: 15px; color: #673AB7; margin-bottom: 12px;">❓ ¿Por qué sacársela?</h3>
                    <p style="font-size: 13px; color: #ccc; line-height: 1.6; margin-bottom: 15px;">
                        Para poder <strong>transmitir</strong>. Escuchar es legal y libre, pero para apretar el botón de hablar
                        y que te oigan hasta en Japón, necesitas tu "matrícula".
                    </p>

                    <h3 style="font-size: 15px; color: #673AB7; margin-bottom: 12px;">📋 El proceso en España</h3>

                    <ul class="license-steps">
                        <li class="license-step">
                            <div class="step-title">Examen</div>
                            <div class="step-desc">
                                Un test de 30 preguntas (normativa) y 30 de técnica (electricidad básica).
                                Hay apps gratuitas para entrenar:
                                <ul class="step-list">
                                    <a href="https://www.ure.es/examenes/" target="_blank">www.ure.es/examenes/</a>
                                    <a href="https://www.fediea.org/examen" target="_blank">www.fediea.org/examen</a>
                                    <a href="https://radioclubquijotes.org/examen" target="_blank">radioclubquijotes.org/examen</a>
                            </div>
                        </li>
                        <li class="license-step">
                            <div class="step-title">Tasa</div>
                            <div class="step-desc">
                                Un pago único de unos <span class="highlight">125€-150€</span>.
                            </div>
                        </li>
                        <li class="license-step">
                            <div class="step-title">Tu Indicativo</div>
                            <div class="step-desc">
                                Recibirás tu nombre oficial (ej. EA1XXX). Es para toda la vida, <strong>no hay cuotas mensuales</strong>.
                            </div>
                        </li>
                    </ul>

                    <div class="key-concept">
                        <span class="key-concept-label">📍 Dato local</span>
                        <p class="key-concept-text">
                            En Cantabria, el examen se hace en Santander y el ambiente es muy familiar.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BLOQUE 5 -->
        <div class="block-section" onclick="toggleBlock(this)">
            <div class="block-header">
                <div class="block-title">
                    <div class="block-number">5</div>
                    <div>
                        <h2>Los "Extras"</h2>
                        <p class="block-subtitle">El Conejo de Alicia</p>
                    </div>
                </div>
                <span class="block-expand-arrow">▼</span>
            </div>
            <div class="block-content">
                <div class="block-content-inner">
                    <div class="block-intro">
                        Una vez tienes la base, <strong>eliges tu propia aventura</strong>. La radio no es solo hablar por micro.
                    </div>

                    <div class="extras-grid">
                        <div class="extra-card">
                            <div class="extra-icon">💻</div>
                            <div class="extra-title">Modos Digitales (FT8)</div>
                            <div class="extra-desc">
                                Conecta la radio al PC y deja que tu ordenador haga contactos por todo el mundo mientras tú tomas un café.
                            </div>
                        </div>

                        <div class="extra-card">
                            <div class="extra-icon">⚡</div>
                            <div class="extra-title">CW (Morse)</div>
                            <div class="extra-desc">
                                El modo más eficiente del mundo. Con muy poca potencia llegas al otro lado del planeta.
                            </div>
                        </div>

                        <div class="extra-card">
                            <div class="extra-icon">📺</div>
                            <div class="extra-title">SSTV</div>
                            <div class="extra-desc">
                                Televisión de Barrido Lento. Envía y recibe fotos por radio. ¡Incluso puedes bajar fotos que emite la Estación Espacial Internacional (ISS)!
                            </div>
                        </div>

                        <div class="extra-card">
                            <div class="extra-icon">🎈</div>
                            <div class="extra-title">Caza de Sondas AEMET</div>
                            <div class="extra-desc">
                                Los globos meteorológicos que lanzan cada día emiten su posición. Puedes ir con tu radio y tu móvil a "cazarlos" cuando caen en el monte.
                            </div>
                        </div>

                        <div class="extra-card">
                            <div class="extra-icon">🚨</div>
                            <div class="extra-title">REMER</div>
                            <div class="extra-desc">
                                La Red de Emergencias. Pon tu equipo al servicio de Protección Civil cuando todo lo demás falla.
                            </div>
                        </div>

                        <div class="extra-card">
                            <div class="extra-icon">🌌</div>
                            <div class="extra-title">Radioastronomía</div>
                            <div class="extra-desc">
                                ¡Escucha el ruido del Sol o de Júpiter con una antena casera!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="disclaimer">
            💡 Esta guía es solo el punto de partida. La radioafición es un universo en expansión donde cada semana descubres algo nuevo.
            No hay un camino único: algunos se vuelven cazadores de DX (contactos lejanos), otros constructores de equipos, otros experimentadores digitales...
            <strong>Elige tu propia aventura</strong>.
        </div>
    </div>

    <script>
        function toggleBlock(element) {
            // Cerrar otros bloques abiertos (opcional, comenta si quieres múltiples abiertos)
            // document.querySelectorAll('.block-section').forEach(block => {
            //     if (block !== element) {
            //         block.classList.remove('expanded');
            //     }
            // });

            // Toggle el bloque actual
            element.classList.toggle('expanded');
        }

        // Opcional: expandir el primer bloque por defecto
        // document.addEventListener('DOMContentLoaded', function() {
        //     document.querySelector('.block-section').classList.add('expanded');
        // });
    </script>
</body>
</html>
