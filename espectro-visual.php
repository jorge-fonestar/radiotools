<?php
require_once 'nav.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espectro Radioeléctrico - Sistema de Propagación HF</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: #0a0a0a;
            padding: 10px;
            min-height: 100vh;
            color: #ffffff;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .espectro-container {
            background: #1a1a1a;
            border-radius: 15px;
            border: 1px solid #333;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            margin-top: 20px;
        }
        
        .espectro-title {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 10px;
            font-size: 2em;
        }
        
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
            font-size: 0.9em;
        }
        
        .spectrum-bar {
            display: flex;
            height: 60px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .spectrum-segment {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.7em;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: transform 0.2s;
            position: relative;
        }
        
        .spectrum-segment:hover {
            transform: scale(1.05);
            z-index: 10;
        }
        
        .bands-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .band-card {
            background: #2a2a2a;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-left: 5px solid;
            border: 1px solid #333;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .band-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            border-color: var(--band-color, #333);
        }
        
        .band-name {
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .band-freq {
            color: #888;
            font-size: 0.85em;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .band-uses {
            color: #ccc;
            font-size: 0.9em;
            line-height: 1.6;
        }
        
        .use-tag {
            display: inline-block;
            background: #1a1a1a;
            border: 1px solid #333;
            padding: 4px 10px;
            border-radius: 12px;
            margin: 3px;
            font-size: 0.8em;
            color: #ccc;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .use-tag:hover {
            background: #333;
            color: #4CAF50;
            border-color: #4CAF50;
            transform: translateY(-2px);
        }
        
        .tooltip {
            position: fixed;
            background: #1a202c;
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 0.85em;
            max-width: 300px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            line-height: 1.6;
        }
        
        .tooltip.show {
            opacity: 1;
        }
        
        .tooltip-title {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 8px;
            color: #4CAF50;
        }
        
        .tooltip-freq {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        
        .tooltip-content {
            margin-top: 5px;
        }
        
        .legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85em;
            color: #ccc;
        }
        
        .legend-color {
            width: 30px;
            height: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php renderNavMenu('espectro-visual.php'); ?>
        
        <div class="header">
            <h1>🌈 Espectro Radioeléctrico</h1>
            <div class="update-time">
                Visualizador Interactivo de Frecuencias
            </div>
        </div>

        <div class="espectro-container">
            <h1 class="espectro-title">📡 Espectro Radioeléctrico</h1>
            <p class="subtitle">De 3 Hz a 300 GHz y más allá - Pasa el ratón para más información</p>
            
            <div class="spectrum-bar">
                <div class="spectrum-segment" style="background: #e53e3e; flex: 0.5;" data-band="elf">ELF/VLF</div>
                <div class="spectrum-segment" style="background: #dd6b20; flex: 1;" data-band="lf">LF/MF</div>
                <div class="spectrum-segment" style="background: #d69e2e; flex: 2;" data-band="hf">HF</div>
                <div class="spectrum-segment" style="background: #38a169; flex: 3;" data-band="vhf">VHF</div>
                <div class="spectrum-segment" style="background: #3182ce; flex: 4;" data-band="uhf">UHF</div>
                <div class="spectrum-segment" style="background: #5a67d8; flex: 3;" data-band="shf">SHF</div>
                <div class="spectrum-segment" style="background: #805ad5; flex: 2;" data-band="ehf">EHF</div>
                <div class="spectrum-segment" style="background: #d53f8c; flex: 1;" data-band="light">IR/Luz</div>
            </div>

            <div class="bands-grid">
                <div class="band-card" style="border-color: #e53e3e;">
                    <div class="band-name" style="color: #e53e3e;">ELF/VLF/LF</div>
                    <div class="band-freq">3 Hz - 300 kHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="submarinos">🚢 Submarinos</span>
                        <span class="use-tag" data-use="navegacion">⚓ Navegación</span>
                        <span class="use-tag" data-use="rfid">🏷️ RFID</span>
                    </div>
                </div>

                <div class="band-card" style="border-color: #dd6b20;">
                    <div class="band-name" style="color: #dd6b20;">MF</div>
                    <div class="band-freq">300 kHz - 3 MHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="radioam">📻 Radio AM</span>
                        <span class="use-tag" data-use="maritima">🛳️ Marítima</span>
                        <span class="use-tag" data-use="radioham-mf">📡 Radioaficionados</span>
                    </div>
                </div>

                <div class="band-card" style="border-color: #d69e2e;">
                    <div class="band-name" style="color: #d69e2e;">HF</div>
                    <div class="band-freq">3 - 30 MHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="ondacorta">🌍 Onda Corta</span>
                        <span class="use-tag" data-use="aviacion-hf">✈️ Aviación</span>
                        <span class="use-tag" data-use="militar">🎖️ Militar</span>
                        <span class="use-tag" data-use="radioham-hf">📻 Radioaficionados</span>
                    </div>
                </div>

                <div class="band-card" style="border-color: #38a169;">
                    <div class="band-name" style="color: #38a169;">VHF</div>
                    <div class="band-freq">30 - 300 MHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="radiofm">📻 FM 88-108 MHz</span>
                        <span class="use-tag" data-use="aviacion-vhf">✈️ Aviación 108-137 MHz</span>
                        <span class="use-tag" data-use="tv">📺 TV</span>
                        <span class="use-tag" data-use="emergencias">🚨 Emergencias</span>
                    </div>
                </div>

                <div class="band-card" style="border-color: #3182ce;">
                    <div class="band-name" style="color: #3182ce;">UHF</div>
                    <div class="band-freq">300 MHz - 3 GHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="moviles">📱 Móviles 4G/5G</span>
                        <span class="use-tag" data-use="tvdigital">📺 TV Digital</span>
                        <span class="use-tag" data-use="gps">🛰️ GPS</span>
                        <span class="use-tag" data-use="wifi24">📶 WiFi 2.4 GHz</span>
                        <span class="use-tag" data-use="bluetooth">🔵 Bluetooth</span>
                    </div>
                </div>

                <div class="band-card" style="border-color: #5a67d8;">
                    <div class="band-name" style="color: #5a67d8;">SHF</div>
                    <div class="band-freq">3 - 30 GHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="5g">📡 5G</span>
                        <span class="use-tag" data-use="wifi5">📶 WiFi 5 GHz</span>
                        <span class="use-tag" data-use="satelites">🛰️ Satélites</span>
                        <span class="use-tag" data-use="radar">🎯 Radar</span>
                        <span class="use-tag" data-use="tvsat">📺 TV Satelital</span>
                    </div>
                </div>

                <div class="band-card" style="border-color: #805ad5;">
                    <div class="band-name" style="color: #805ad5;">EHF/mmWave</div>
                    <div class="band-freq">30 - 300 GHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="5gmmwave">📡 5G mmWave</span>
                        <span class="use-tag" data-use="radar77">🚗 Radar 77 GHz</span>
                        <span class="use-tag" data-use="investigacion">🔬 Investigación</span>
                        <span class="use-tag" data-use="astronomia">🌌 Astronomía</span>
                    </div>
                </div>

                <div class="band-card" style="border-color: #d53f8c;">
                    <div class="band-name" style="color: #d53f8c;">Más allá</div>
                    <div class="band-freq">> 300 GHz</div>
                    <div class="band-uses">
                        <span class="use-tag" data-use="infrarrojo">🌡️ Infrarrojo</span>
                        <span class="use-tag" data-use="luzvisible">💡 Luz Visible</span>
                        <span class="use-tag" data-use="ultravioleta">🔆 Ultravioleta</span>
                        <span class="use-tag" data-use="rayosx">⚡ Rayos X</span>
                    </div>
                </div>
            </div>

            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #e53e3e;"></div>
                    <span>Comunicación profunda</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #dd6b20;"></div>
                    <span>Radio clásica</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #d69e2e;"></div>
                    <span>Onda corta</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #38a169;"></div>
                    <span>FM & Aviación</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #3182ce;"></div>
                    <span>Móviles & WiFi</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #5a67d8;"></div>
                    <span>Satélites & 5G</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #805ad5;"></div>
                    <span>Muy alta frecuencia</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #d53f8c;"></div>
                    <span>Óptico</span>
                </div>
            </div>
        </div>
    </div>

    <div class="tooltip" id="tooltip"></div>

    <script>
        // [El JavaScript del tooltip se mantiene igual que en el archivo original]
        const tooltipData = {
            // Datos de bandas principales
            bands: {
                elf: {
                    title: 'ELF/VLF - Frecuencias Extremadamente Bajas',
                    freq: '3 Hz - 30 kHz',
                    content: 'Penetran profundamente en el agua y la tierra. Se usan principalmente para comunicación con submarinos sumergidos. Las ondas viajan miles de kilómetros. También se usan en estudios geofísicos y detección de terremotos.'
                },
                lf: {
                    title: 'LF/MF - Frecuencias Bajas y Medias',
                    freq: '30 kHz - 3 MHz',
                    content: 'Banda de radio AM (530-1710 kHz). Las ondas rebotan en la ionosfera durante la noche, permitiendo alcances de cientos de kilómetros. Incluye sistemas de navegación y balizas horarias como WWV.'
                },
                hf: {
                    title: 'HF - Frecuencias Altas (Onda Corta)',
                    freq: '3 - 30 MHz',
                    content: 'Las ondas reflejan en la ionosfera permitiendo comunicaciones mundiales. Populares entre radioaficionados, estaciones internacionales, y comunicación militar. La propagación varía según la hora del día y la actividad solar.'
                },
                vhf: {
                    title: 'VHF - Frecuencias Muy Altas',
                    freq: '30 - 300 MHz',
                    content: 'Principalmente línea de vista. Incluye FM (88-108 MHz), comunicación aeronáutica (108-137 MHz), TV analógica, y servicios de emergencia. Radio clara con poco ruido.'
                },
                uhf: {
                    title: 'UHF - Frecuencias Ultra Altas',
                    freq: '300 MHz - 3 GHz',
                    content: 'Rango más usado para telefonía móvil, WiFi 2.4 GHz, Bluetooth, GPS, y TV digital. Las ondas son más cortas y requieren más potencia, pero permiten mayor densidad de canales.'
                },
                shf: {
                    title: 'SHF - Frecuencias Super Altas',
                    freq: '3 - 30 GHz',
                    content: 'Banda de microondas usada para enlaces satelitales, WiFi 5 GHz, radar, y conexiones punto a punto de alta velocidad. Requiere antenas direccionales para largas distancias.'
                },
                ehf: {
                    title: 'EHF - Frecuencias Extremadamente Altas',
                    freq: '30 - 300 GHz',
                    content: 'Ondas milimétricas (mmWave) usadas en 5G de alta velocidad, radar automotriz (77 GHz), y aplicaciones científicas. Muy alta capacidad pero alcance limitado, absorbidas por el oxígeno y la lluvia.'
                },
                light: {
                    title: 'Más allá del Radio - Espectro Óptico',
                    freq: '> 300 GHz',
                    content: 'Incluye infrarrojo (controles remotos, fibra óptica), luz visible (430-770 THz), ultravioleta (esterilización), y rayos X (medicina). Comunicación por fibra óptica alcanza terabits/segundo.'
                }
            },
            // Datos de aplicaciones específicas
            uses: {
                submarinos: {
                    title: '🚢 Comunicación Submarina',
                    freq: '3-30 Hz (ELF), 3-30 kHz (VLF)',
                    content: 'Las frecuencias extremadamente bajas penetran el agua de mar. ELF alcanza cientos de metros de profundidad pero transmite muy lentamente (pocos caracteres por minuto). Sistema TACAMO de la US Navy.'
                },
                navegacion: {
                    title: '⚓ Radionavegación',
                    freq: '90-110 kHz (LORAN), 283.5-325 kHz',
                    content: 'Sistemas como LORAN-C (obsoleto) y radiofaros marítimos. Alcance de cientos de km. Complementados ahora por GPS pero aún usados como backup.'
                },
                rfid: {
                    title: '🏷️ RFID - Identificación por Radiofrecuencia',
                    freq: '125-134 kHz (LF), 13.56 MHz (HF)',
                    content: 'Etiquetas pasivas para control de acceso, inventario, pasaportes electrónicos, pago sin contacto (NFC). LF: corto alcance (cm), atraviesa metal. HF/NFC: hasta 10 cm.'
                },
                radioam: {
                    title: '📻 Radio AM (Amplitud Modulada)',
                    freq: '530-1710 kHz',
                    content: 'Radiodifusión de onda media. Por el día: alcance local 50-100 km. Por la noche: las ondas rebotan en ionosfera alcanzando 1000+ km. Susceptible a interferencias eléctricas.'
                },
                maritima: {
                    title: '🛳️ Comunicación Marítima MF',
                    freq: '2-3 MHz',
                    content: 'Banda de socorro 2182 kHz (obsoleta, ahora digital). Comunicación barco-costa y barco-barco de medio alcance. Sistema GMDSS para seguridad marítima.'
                },
                'radioham-mf': {
                    title: '📡 Radioaficionados MF',
                    freq: '1.8-2.0 MHz (banda 160m)',
                    content: 'Banda nocturna de radioaficionados. Durante el día apenas alcance local, por la noche comunicaciones continentales. Popular para concursos nocturnos.'
                },
                ondacorta: {
                    title: '🌍 Radiodifusión Onda Corta',
                    freq: '3-30 MHz (bandas de 120m a 11m)',
                    content: 'Emisoras internacionales como BBC, VOA, DW. Bandas principales: 49m (5.9-6.2 MHz), 41m, 31m, 25m, 19m, 16m, 13m. Alcance mundial aprovechando reflexión ionosférica.'
                },
                'aviacion-hf': {
                    title: '✈️ Comunicación Aeronáutica HF',
                    freq: '2-30 MHz (varias bandas)',
                    content: 'Para vuelos transoceánicos donde no llega VHF. Bandas asignadas entre 2.8-22 MHz. Comunicación avión-control oceánico. Sujeto a propagación ionosférica.'
                },
                militar: {
                    title: '🎖️ Comunicaciones Militares',
                    freq: 'Múltiples bandas HF/VHF/UHF/SHF',
                    content: 'Uso extensivo de HF (3-30 MHz) para alcance estratégico, VHF/UHF táctico, y SHF satelital. Sistemas cifrados, frecuencias ágiles. SATCOM en banda X (8-12 GHz).'
                },
                'radioham-hf': {
                    title: '📻 Radioaficionados HF',
                    freq: '3.5-4 (80m), 7-7.3 (40m), 14-14.35 (20m), 21-21.45 (15m), 28-29.7 MHz (10m)',
                    content: 'Bandas más populares para contactos DX (larga distancia). 20m: mejor banda diurna mundial. 40m: día y noche. 80m: nocturna. Modos: SSB, CW, digitales (FT8).'
                },
                radiofm: {
                    title: '📻 Radio FM - Frecuencia Modulada',
                    freq: '87.5-108 MHz',
                    content: 'Audio de alta fidelidad estéreo. Alcance típico 50-80 km. Menos interferencias que AM. Incluye RDS (Radio Data System) para mostrar información. Potencias: 100W-100kW.'
                },
                'aviacion-vhf': {
                    title: '✈️ Comunicación Aeronáutica VHF',
                    freq: '118-137 MHz',
                    content: '121.5 MHz: emergencias. 122-123 MHz: aviación general. Control tórres: 118-128 MHz. Alcance línea de vista ~300 km a altitud crucero. Canales cada 25/8.33 kHz.'
                },
                tv: {
                    title: '📺 Televisión VHF',
                    freq: '54-88 MHz (canales 2-6), 174-216 MHz (7-13)',
                    content: 'TV analógica tradicional (mayoría apagada). Ahora reutilizado para TV digital (DVB-T/ATSC) y servicios móviles. Buena penetración en edificios.'
                },
                emergencias: {
                    title: '🚨 Servicios de Emergencia VHF',
                    freq: '148-174 MHz',
                    content: 'Policía, bomberos, ambulancias. TETRA en 380-400 MHz. PMR446: 446 MHz (walkie-talkies civiles). Canal 16 marino: 156.8 MHz. Sistemas digitales encriptados.'
                },
                moviles: {
                    title: '📱 Telefonía Móvil 4G/5G',
                    freq: '700, 800, 900, 1800, 2100, 2600 MHz (sub-6 GHz)',
                    content: 'GSM 900/1800, UMTS 2100, LTE bandas 3/7/20/28. 5G FR1: 3.5 GHz (banda C). Cada operador usa diferentes bandas. Mayor frecuencia = más capacidad pero menor cobertura.'
                },
                tvdigital: {
                    title: '📺 Televisión Digital Terrestre',
                    freq: '470-608 MHz (canales 21-37), 614-698 MHz',
                    content: 'DVB-T2 en Europa, ATSC 3.0 en América. Múltiples programas por canal (multiplexado). Parte del espectro liberado para 5G (dividendo digital).'
                },
                gps: {
                    title: '🛰️ GPS y Sistemas GNSS',
                    freq: 'GPS L1: 1575.42 MHz, L2: 1227.6 MHz, L5: 1176.45 MHz',
                    content: 'GPS (USA), GLONASS (Rusia), Galileo (EU), BeiDou (China). Señales muy débiles, requieren vista del cielo. L1 civil, L2/L5 mejor precisión. DGPS: corrección diferencial.'
                },
                wifi24: {
                    title: '📶 WiFi 2.4 GHz',
                    freq: '2.400-2.4835 GHz (canales 1-13)',
                    content: 'IEEE 802.11 b/g/n/ax. 14 canales (solo 1, 6, 11 sin solapamiento). Banda ISM libre. Alcance ~100m interior. Interferencias: Bluetooth, microondas. Velocidades hasta 600 Mbps (WiFi 6).'
                },
                bluetooth: {
                    title: '🔵 Bluetooth',
                    freq: '2.400-2.4835 GHz (79 canales de 1 MHz)',
                    content: 'Salto de frecuencia (FHSS) 1600 saltos/seg. BT Classic: audio, datos. BLE (Low Energy): sensores, IoT. Alcance clase 2: 10m, clase 1: 100m. Versión 5.x: 2 Mbps.'
                },
                '5g': {
                    title: '📡 5G Sub-6 GHz',
                    freq: '3.3-3.8 GHz (banda C), 3.4-3.6 GHz típico',
                    content: '5G FR1 (Frequency Range 1). Banda n78: 3.5 GHz más común. Balance entre cobertura y capacidad. Velocidades 100-900 Mbps. Menor latencia que 4G (<20ms).'
                },
                wifi5: {
                    title: '📶 WiFi 5 y 6 GHz',
                    freq: '5.15-5.875 GHz (WiFi 5), 5.925-7.125 GHz (WiFi 6E)',
                    content: '802.11ac/ax. Menos congestionado que 2.4 GHz. Canales de 20/40/80/160 MHz. Velocidades hasta 9.6 Gbps (WiFi 6). Menor alcance pero más capacidad. WiFi 6E añade 6 GHz.'
                },
                satelites: {
                    title: '🛰️ Comunicaciones Satelitales',
                    freq: 'C: 4-8 GHz, Ku: 12-18 GHz, Ka: 27-40 GHz',
                    content: 'Banda C: resistente a lluvia, antenas grandes. Ku: TV satelital (DTH), VSAT. Ka: satélites HTS (alta capacidad), Starlink. Latencia geoestacionaria: ~500ms.'
                },
                radar: {
                    title: '🎯 Sistemas Radar',
                    freq: 'Banda X: 8-12 GHz, Banda Ku: 12-18 GHz',
                    content: 'Radar meteorológico: banda C (5 GHz) y X. Radar marítimo: banda X (9 GHz). Control tráfico aéreo: banda S (3 GHz). Resolución mejora con mayor frecuencia.'
                },
                tvsat: {
                    title: '📺 TV Satelital (DTH)',
                    freq: '10.7-12.75 GHz (Ku-band)',
                    content: 'Direct To Home. Astra, Eutelsat, DirecTV. LNB convierte a frecuencias intermedias. Requiere plato parabólico apuntado al satélite. 100+ canales por satélite.'
                },
                '5gmmwave': {
                    title: '📡 5G mmWave (Ondas Milimétricas)',
                    freq: '24-40 GHz (FR2), típico 26/28/39 GHz',
                    content: '5G de ultra alta velocidad: hasta 10 Gbps. Alcance muy limitado: 200-400m. Bloqueado por edificios, personas, lluvia. Uso en estadios, estaciones, zonas densas urbanas.'
                },
                radar77: {
                    title: '🚗 Radar Automotriz',
                    freq: '76-81 GHz (banda W)',
                    content: 'Sistemas ADAS: control crucero adaptativo, frenado automático, punto ciego. Alcance largo: 77 GHz. Corto alcance: 79 GHz. Resolución <1°. Funciona con mal tiempo.'
                },
                investigacion: {
                    title: '🔬 Investigación Científica EHF',
                    freq: '30-300 GHz',
                    content: 'Espectroscopía molecular, imagen de seguridad (body scanners), comunicaciones experimentales de muy alta capacidad. Ventanas atmosféricas limitadas por absorción.'
                },
                astronomia: {
                    title: '🌌 Radioastronomía mmWave',
                    freq: '30-950 GHz',
                    content: 'Observación de moléculas interestelares, estudio del CMB, interferometría (ALMA). Banda Q: 40-50 GHz, banda W: 75-110 GHz. Requiere zonas de silencio radio.'
                },
                infrarrojo: {
                    title: '🌡️ Infrarrojo',
                    freq: '300 GHz - 430 THz',
                    content: 'IR lejano: sensores térmicos. IR medio: calefacción. IR cercano: controles remotos (38 kHz modulados), fibra óptica (1310/1550 nm), comunicación óptica libre (FSO).'
                },
                luzvisible: {
                    title: '💡 Luz Visible',
                    freq: '430-770 THz (390-700 nm)',
                    content: 'Rojo: 430 THz, Violeta: 770 THz. Comunicación LiFi: transmisión de datos por luz LED. Fibra óptica multimodal. Iluminación, fotografía, displays.'
                },
                ultravioleta: {
                    title: '🔆 Ultravioleta',
                    freq: '770 THz - 30 PHz (10-400 nm)',
                    content: 'UVA: luz negra, detección forense. UVB/UVC: esterilización germicida (254 nm), purificación agua. Dañino para piel/ojos. Bloqueado por atmósfera (ozono).'
                },
                rayosx: {
                    title: '⚡ Rayos X y Gamma',
                    freq: '> 30 PHz (<10 nm)',
                    content: 'Rayos X: diagnóstico médico, seguridad aeropuertos, cristalografía. Rayos gamma: radioterapia, astronomía de alta energía. Radiación ionizante, requiere protección.'
                }
            }
        };

        const tooltip = document.getElementById('tooltip');
        let currentTarget = null;

        function showTooltip(e, data) {
            tooltip.innerHTML = `
                <div class="tooltip-title">${data.title}</div>
                <div class="tooltip-freq">${data.freq}</div>
                <div class="tooltip-content">${data.content}</div>
            `;
            
            const rect = e.target.getBoundingClientRect();
            let left = e.clientX + 15;
            let top = e.clientY + 15;
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.classList.add('show');
        }

        function hideTooltip() {
            tooltip.classList.remove('show');
        }

        // Event listeners para tooltips (versión simplificada)
        document.querySelectorAll('.spectrum-segment, .use-tag').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                const key = element.dataset.band || element.dataset.use;
                const data = tooltipData.bands[key] || tooltipData.uses[key];
                if (data) {
                    currentTarget = element;
                    showTooltip(e, data);
                }
            });
            
            element.addEventListener('mouseleave', () => {
                hideTooltip();
            });
        });
    </script>
</body>
</html>