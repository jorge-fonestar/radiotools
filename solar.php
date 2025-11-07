<?php
require_once 'nav.php';

// Cache de datos para no sobrecargar el servidor
$cache_file = 'solar_cache.json';
$cache_time = 300; // 5 minutos

// Función auxiliar para convertir XML a array
function xmlToArray($xml) {
    // Los datos están dentro de <solardata>
    $solar = $xml->solardata;

    $data = [
        'solarflux' => (string)$solar->solarflux,
        'sunspots' => (string)$solar->sunspots,
        'kindex' => trim((string)$solar->kindex),
        'aindex' => trim((string)$solar->aindex),
        'aurora' => trim((string)$solar->aurora),
        'updated' => (string)$solar->updated,
        'xray' => (string)$solar->xray,
        'solarwind' => (string)$solar->solarwind,
        'magneticfield' => trim((string)$solar->magneticfield),
        'geomagfield' => (string)$solar->geomagfield,
        'signalnoise' => (string)$solar->signalnoise,
        'heliumline' => (string)$solar->heliumline,
        'protonflux' => (string)$solar->protonflux,
        'electonflux' => (string)$solar->electonflux,
    ];

    // Obtener condiciones de bandas (solo de día/noche según hora)
    if (isset($solar->calculatedconditions->band)) {
        $hour = (int)date('H');
        $isDay = ($hour >= 6 && $hour < 20);
        $timeFilter = $isDay ? 'day' : 'night';

        foreach ($solar->calculatedconditions->band as $band) {
            $name = (string)$band['name'];
            $time = (string)$band['time'];

            if ($time === $timeFilter) {
                $condition = (string)$band;
                $data[$name] = strtolower($condition);
            }
        }
    }

    // Obtener condiciones VHF
    if (isset($solar->calculatedvhfconditions->phenomenon)) {
        foreach ($solar->calculatedvhfconditions->phenomenon as $phenomenon) {
            $name = (string)$phenomenon['name'];
            $location = (string)$phenomenon['location'];
            $condition = (string)$phenomenon;
            
            // Crear clave única combinando nombre y ubicación
            $key = $name . '_' . $location;
            $data[$key] = strtolower($condition);
        }
    }

    return $data;
}

// Función para obtener datos
function getSolarData() {
    global $cache_file, $cache_time;

    // Verificar cache
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $cached = file_get_contents($cache_file);
        $xml = simplexml_load_string($cached);
        if ($xml) {
            return xmlToArray($xml);
        }
    }

    // Obtener nuevos datos
    $url = "https://www.hamqsl.com/solarxml.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        // Guardar XML en cache
        file_put_contents($cache_file, $response);

        // Convertir XML a array
        $xml = simplexml_load_string($response);
        if ($xml) {
            return xmlToArray($xml);
        }
    }

    return false;
}

// Función para determinar color según condiciones
function getConditionColor($value) {
    switch(strtolower($value)) {
        case 'good': return '#4CAF50';
        case 'fair': return '#FFC107';
        case 'poor': return '#F44336';
        default: return '#9E9E9E';
    }
}

// Función para traducir condiciones de bandas
function translateCondition($value) {
    switch(strtolower($value)) {
        case 'good': return 'Buena';
        case 'fair': return 'Regular';
        case 'poor': return 'Mala';
        default: return ucfirst($value);
    }
}

// Función para traducir condiciones VHF
function translateVHFCondition($value) {
    switch(strtolower($value)) {
        case 'band closed': return 'Banda Cerrada';
        case 'band open': return 'Banda Abierta';
        case 'band enhanced': return 'Banda Mejorada';
        case 'band possible': return 'Banda Posible';
        default: return ucfirst($value);
    }
}

// Función para obtener color de condiciones VHF
function getVHFConditionColor($value) {
    switch(strtolower($value)) {
        case 'band open': 
        case 'band enhanced': 
            return '#4CAF50'; // Verde
        case 'band possible': 
            return '#FFC107'; // Amarillo
        case 'band closed': 
            return '#F44336'; // Rojo
        default: 
            return '#9E9E9E'; // Gris
    }
}

// Función para evaluar SFI
function evaluateSFI($sfi) {
    if ($sfi < 70) return ['color' => '#F44336', 'text' => "Pobre", 'icon' => '❌'];
    if ($sfi < 90) return ['color' => '#FF9800', 'text' => "Regular", 'icon' => '⚠️'];
    if ($sfi < 120) return ['color' => '#4CAF50', 'text' => "Buena", 'icon' => '✅'];
    if ($sfi < 150) return ['color' => '#2E7D32', 'text' => "Muy Buena", 'icon' => '🟢'];
    return ['color' => '#1B5E20', 'text' => "Excelente", 'icon' => '🌟'];
}

// Función para evaluar K-Index
function evaluateKIndex($k) {
    if ($k <= 1) return ['color' => '#4CAF50', 'text' => "Muy Tranquilo", 'icon' => '😊'];
    if ($k <= 3) return ['color' => '#8BC34A', 'text' => "Tranquilo", 'icon' => '👍'];
    if ($k == 4) return ['color' => '#FFC107', 'text' => "Activo", 'icon' => '⚡'];
    if ($k == 5) return ['color' => '#FF9800', 'text' => "Tormenta G1", 'icon' => '🌩️'];
    if ($k <= 7) return ['color' => '#F44336', 'text' => "Tormenta G2-G3", 'icon' => '⛈️'];
    return ['color' => '#B71C1C', 'text' => "Tormenta Severa", 'icon' => '🌪️'];
}

// Función para evaluar A-Index
function evaluateAIndex($a) {
    if ($a <= 7) return ['color' => '#4CAF50', 'text' => "Tranquilo", 'icon' => '✅'];
    if ($a <= 15) return ['color' => '#8BC34A', 'text' => "Estable", 'icon' => '👍'];
    if ($a <= 29) return ['color' => '#FFC107', 'text' => "Alterado", 'icon' => '⚠️'];
    if ($a <= 49) return ['color' => '#FF9800', 'text' => "Activo", 'icon' => '⚡'];
    if ($a <= 99) return ['color' => '#F44336', 'text' => "Tormenta", 'icon' => '⛈️'];
    return ['color' => '#B71C1C', 'text' => "Severa", 'icon' => '🌪️'];
}

// Función para evaluar X-Ray
function evaluateXRay($xray) {
    $class = substr($xray, 0, 1);
    switch($class) {
        case 'A': return ['color' => '#4CAF50', 'text' => "Muy Bajo", 'icon' => '🟢'];
        case 'B': return ['color' => '#8BC34A', 'text' => "Bajo", 'icon' => '✅'];
        case 'C': return ['color' => '#FFC107', 'text' => "Moderado", 'icon' => '⚠️'];
        case 'M': return ['color' => '#FF9800', 'text' => "Fuerte", 'icon' => '🔶'];
        case 'X': return ['color' => '#F44336', 'text' => "Extremo", 'icon' => '🔥'];
        default: return ['color' => '#666', 'text' => "$xray", 'icon' => '❓'];
    }
}

// Función para evaluar Solar Wind
function evaluateSolarWind($wind) {
    $w = floatval($wind);
    if ($w < 300) return ['color' => '#4CAF50', 'text' => "Lento", 'icon' => '🌬️'];
    if ($w < 500) return ['color' => '#8BC34A', 'text' => "Normal", 'icon' => '💨'];
    if ($w < 700) return ['color' => '#FFC107', 'text' => "Rápido", 'icon' => '⚡'];
    if ($w < 900) return ['color' => '#FF9800', 'text' => "Muy Rápido", 'icon' => '🌪️'];
    return ['color' => '#F44336', 'text' => "Extremo", 'icon' => '🔥'];
}

// Obtener datos
$data = getSolarData();
$error = false;

if (!$data) {
    $error = true;
    $data = [
        'solarflux' => '--',
        'sunspots' => '--',
        'kindex' => '--',
        'aindex' => '--',
        'updated' => 'Error'
    ];
}

// Evaluaciones
$sfi_eval = $data['solarflux'] !== '--' ? evaluateSFI(intval($data['solarflux'])) : ['color' => '#666', 'text' => 'N/A', 'icon' => '❓'];
$k_eval = $data['kindex'] !== '--' ? evaluateKIndex(intval($data['kindex'])) : ['color' => '#666', 'text' => 'N/A', 'icon' => '❓'];
$a_eval = $data['aindex'] !== '--' ? evaluateAIndex(intval($data['aindex'])) : ['color' => '#666', 'text' => 'N/A', 'icon' => '❓'];
$xray_eval = isset($data['xray']) && $data['xray'] !== '--' ? evaluateXRay($data['xray']) : ['color' => '#666', 'text' => 'N/A', 'icon' => '❓'];
$wind_eval = isset($data['solarwind']) && $data['solarwind'] !== '--' ? evaluateSolarWind($data['solarwind']) : ['color' => '#666', 'text' => 'N/A', 'icon' => '❓'];

// Calcular recomendación general
$recommendation = '';
if (!$error) {
    $sfi = intval($data['solarflux']);
    $k = intval($data['kindex']);
    $a = intval($data['aindex']);
    
    if ($sfi > 100 && $k < 4 && $a < 20) {
        $recommendation = '🎯 Excelentes condiciones para DX';
    } elseif ($sfi > 80 && $k < 5 && $a < 30) {
        $recommendation = '📡 Buenas condiciones para HF';
    } elseif ($k > 4 || $a > 30) {
        $recommendation = '⚠️ Condiciones alteradas - Usar bandas bajas';
    } else {
        $recommendation = '📻 Condiciones normales';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Monitor Solar Amateur Radio</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <?php renderNavMenu('solar.php'); ?>
        
        <div class="header">
            <h1>📡 Monitor Solar HF</h1>
            <div class="update-time">
                <?php 
                if (!$error && isset($data['updated'])) {
                    echo "Actualizado: " . $data['updated'];
                } else {
                    echo "Error al obtener datos";
                }
                ?>
            </div>
        </div>
        
        <?php if ($error): ?>
        <div class="error">
            ⚠️ Error al conectar con el servidor
        </div>
        <?php endif; ?>

        <?php if ($recommendation): ?>
        <div class="recommendation">
            <?php echo $recommendation; ?>
        </div>
        <?php endif; ?>

        <?php if (!$error && isset($data['80m-40m'])): ?>
        <div class="bands-section" onclick="this.classList.toggle('expanded')">
            <div class="bands-header">
                <h2 class="bands-title">📻 Condiciones por Banda (<?php echo (date('H') >= 6 && date('H') < 20) ? 'Día' : 'Noche'; ?>)</h2>
                <span class="bands-expand-arrow">▼</span>
            </div>
            <div class="bands-content">
                <div class="band-grid" style="padding: 15px;">
                    <div class="band-item" style="--band-color: <?php echo getConditionColor($data['80m-40m']); ?>">
                        <div class="band-name">80m - 40m</div>
                        <div class="band-status"><?php echo translateCondition($data['80m-40m']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <div class="band-item" style="--band-color: <?php echo getConditionColor($data['30m-20m']); ?>">
                        <div class="band-name">30m - 20m</div>
                        <div class="band-status"><?php echo translateCondition($data['30m-20m']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <div class="band-item" style="--band-color: <?php echo getConditionColor($data['17m-15m']); ?>">
                        <div class="band-name">17m - 15m</div>
                        <div class="band-status"><?php echo translateCondition($data['17m-15m']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <div class="band-item" style="--band-color: <?php echo getConditionColor($data['12m-10m']); ?>">
                        <div class="band-name">12m - 10m</div>
                        <div class="band-status"><?php echo translateCondition($data['12m-10m']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$error && (isset($data['vhf-aurora_northern_hemi']) || isset($data['E-Skip_europe']) || isset($data['E-Skip_north_america']) || isset($data['E-Skip_europe_6m']) || isset($data['E-Skip_europe_4m']))): ?>
        <div class="bands-section" onclick="this.classList.toggle('expanded')">
            <div class="bands-header">
                <h2 class="bands-title">📡 Condiciones VHF/UHF</h2>
                <span class="bands-expand-arrow">▼</span>
            </div>
            <div class="bands-content">
                <div class="band-grid" style="padding: 15px;">
                    <?php if (isset($data['vhf-aurora_northern_hemi'])): ?>
                    <div class="band-item" style="--band-color: <?php echo getVHFConditionColor($data['vhf-aurora_northern_hemi']); ?>">
                        <div class="band-name">Aurora VHF</div>
                        <div class="band-status"><?php echo translateVHFCondition($data['vhf-aurora_northern_hemi']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($data['E-Skip_europe'])): ?>
                    <div class="band-item" style="--band-color: <?php echo getVHFConditionColor($data['E-Skip_europe']); ?>">
                        <div class="band-name">E-Skip Europa</div>
                        <div class="band-status"><?php echo translateVHFCondition($data['E-Skip_europe']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($data['E-Skip_north_america'])): ?>
                    <div class="band-item" style="--band-color: <?php echo getVHFConditionColor($data['E-Skip_north_america']); ?>">
                        <div class="band-name">E-Skip N.América</div>
                        <div class="band-status"><?php echo translateVHFCondition($data['E-Skip_north_america']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($data['E-Skip_europe_6m'])): ?>
                    <div class="band-item" style="--band-color: <?php echo getVHFConditionColor($data['E-Skip_europe_6m']); ?>">
                        <div class="band-name">E-Skip 6m Europa</div>
                        <div class="band-status"><?php echo translateVHFCondition($data['E-Skip_europe_6m']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($data['E-Skip_europe_4m'])): ?>
                    <div class="band-item" style="--band-color: <?php echo getVHFConditionColor($data['E-Skip_europe_4m']); ?>">
                        <div class="band-name">E-Skip 4m Europa</div>
                        <div class="band-status"><?php echo translateVHFCondition($data['E-Skip_europe_4m']); ?></div>
                        <div class="band-indicator"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="main-indicators">
            <!-- SFI -->
            <div class="indicator-card" style="--indicator-color: <?php echo $sfi_eval['color']; ?>" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">📡</span>
                    <div class="indicator-main">
                        <span class="indicator-label">SFI</span>
                        <span class="indicator-value"><?php echo $data['solarflux']; ?></span>
                        <span class="indicator-status"><?php echo $sfi_eval['text']; ?></span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>Solar Flux Index:</strong> Radiación solar a 2800 MHz. Mayor valor indica mejor propagación en bandas HF debido a mayor ionización de la ionosfera.</div>
                        <div class="indicator-scale">Escala: 0-300 (óptimo: 100-200)</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> SFI >150 permite DX en 10-15m incluso con antenas modestas. SFI <70 limita severamente bandas altas.<br><strong>Salud:</strong> Valores altos indican mayor radiación UV que puede afectar la piel. No hay riesgo directo por exposición a radiofrecuencia.</div>
                    </div>
                </div>
            </div>

            <!-- K-Index -->
            <div class="indicator-card" style="--indicator-color: <?php echo $k_eval['color']; ?>" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">🧲</span>
                    <div class="indicator-main">
                        <span class="indicator-label">K-Index</span>
                        <span class="indicator-value"><?php echo $data['kindex']; ?></span>
                        <span class="indicator-status"><?php echo $k_eval['text']; ?></span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>K-Index:</strong> Actividad geomagnética en las últimas 3 horas. Valores bajos (0-3) indican condiciones estables ideales para DX. Valores altos (5+) indican tormentas geomagnéticas.</div>
                        <div class="indicator-scale">Escala: 0-9 (bajo mejor: 0-3 ideal)</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> K>4 causa desvanecimientos (fading) rápidos y auroras que absorben señales en latitudes altas. Comunicaciones polares muy afectadas.<br><strong>Salud:</strong> Tormentas geomagnéticas pueden causar dolores de cabeza y afectar marcapasos en personas sensibles.</div>
                    </div>
                </div>
            </div>

            <!-- A-Index -->
            <div class="indicator-card" style="--indicator-color: <?php echo $a_eval['color']; ?>" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">🌍</span>
                    <div class="indicator-main">
                        <span class="indicator-label">A-Index</span>
                        <span class="indicator-value"><?php echo $data['aindex']; ?></span>
                        <span class="indicator-status"><?php echo $a_eval['text']; ?></span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>A-Index:</strong> Actividad geomagnética promedio de las últimas 24 horas. Indica el nivel de perturbaciones y tormentas geomagnéticas que afectan la propagación.</div>
                        <div class="indicator-scale">Escala: 0-400 (bajo mejor: 0-15 ideal)</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> A>30 degrada notablemente propagación en todas las bandas. Rutas polares casi imposibles. Mejor usar bandas bajas (40m-80m).<br><strong>Salud:</strong> Periodos prolongados de A alto correlacionan con aumento de problemas cardiovasculares en población sensible.</div>
                    </div>
                </div>
            </div>

            <!-- Sunspots -->
            <div class="indicator-card" style="--indicator-color: #FF9800" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">☀️</span>
                    <div class="indicator-main">
                        <span class="indicator-label">Manchas</span>
                        <span class="indicator-value"><?php echo $data['sunspots']; ?></span>
                        <span class="indicator-status">SN</span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>Manchas Solares:</strong> Regiones de intensa actividad magnética en el Sol. Mayor número de manchas solares significa mayor actividad solar, más ionización y mejores condiciones para DX en HF.</div>
                        <div class="indicator-scale">Escala: 0-300+ (más manchas = mejor HF)</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> SN>100 abre bandas 10-15-20m para DX mundial con potencias bajas. SN<20 (mínimo solar) requiere bandas bajas.<br><strong>Salud:</strong> Más manchas = más radiación UV y tormentas solares. Aumenta riesgo de exposición solar. Vuelos polares pueden desviarse.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores Secundarios -->
        <div class="secondary-indicators">
            <!-- X-Ray -->
            <div class="indicator-card" style="--indicator-color: <?php echo $xray_eval['color']; ?>" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">☢️</span>
                    <div class="indicator-main">
                        <span class="indicator-label">Rayos X</span>
                        <span class="indicator-value"><?php echo isset($data['xray']) ? $data['xray'] : '--'; ?></span>
                        <span class="indicator-status"><?php echo $xray_eval['text']; ?></span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>Rayos X:</strong> Fulguraciones solares medidas en rayos X. Clases M y X pueden causar blackouts repentinos en HF debido a ionización excesiva de la capa D.</div>
                        <div class="indicator-scale">Escala: A < B < C < M < X (exponencial)</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> Clase M/X causa apagón total HF en lado diurno (10min-2h). Bandas bajas menos afectadas. VHF/UHF no afectadas.<br><strong>Salud:</strong> Rayos X bloqueados por atmósfera. Sin riesgo directo en superficie. Astronautas y pilotos en altitud extrema sí expuestos.</div>
                    </div>
                </div>
            </div>

            <!-- Solar Wind -->
            <div class="indicator-card" style="--indicator-color: <?php echo $wind_eval['color']; ?>" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">💨</span>
                    <div class="indicator-main">
                        <span class="indicator-label">Viento</span>
                        <span class="indicator-value"><?php echo isset($data['solarwind']) ? round($data['solarwind']) : '--'; ?></span>
                        <span class="indicator-status">km/s</span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>Viento Solar:</strong> Velocidad de las partículas cargadas del Sol. Velocidades >600 km/s pueden causar tormentas geomagnéticas que afectan la propagación.</div>
                        <div class="indicator-scale">Normal: 300-500 km/s | Alto: >700 km/s</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> >700 km/s degrada HF 24-72h después. Auroras visibles en latitudes medias. Scatter auroral posible en VHF.<br><strong>Salud:</strong> El campo magnético terrestre nos protege. Pueden afectarse satélites GPS/comunicaciones. Sin efecto directo en humanos a nivel del mar.</div>
                    </div>
                </div>
            </div>

            <!-- Geomagnetic Field -->
            <?php if (isset($data['geomagfield'])): ?>
            <div class="indicator-card" style="--indicator-color: #4CAF50" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">⚡</span>
                    <div class="indicator-main">
                        <span class="indicator-label">Geomag</span>
                        <span class="indicator-value" style="font-size: 16px;"><?php echo $data['geomagfield']; ?></span>
                        <span class="indicator-status"><?php echo isset($data['magneticfield']) ? $data['magneticfield'] . ' nT' : ''; ?></span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>Campo Geomagnético:</strong> Estado del campo magnético terrestre. Afecta principalmente a propagación en latitudes altas y comunicaciones polares. Importante para auroras.</div>
                        <div class="indicator-scale">QUIET = Estable | STORM = Tormenta activa</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> STORM activa absorción auroral que bloquea señales HF transpolares. Aumenta ruido en bandas bajas.<br><strong>Salud:</strong> Tormentas correlacionan con más migrañas, insomnio y alteraciones del ritmo cardíaco en personas sensibles. Animales migratorios desorientados.</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Signal Noise -->
            <?php if (isset($data['signalnoise'])): ?>
            <div class="indicator-card" style="--indicator-color: #2196F3" onclick="this.classList.toggle('expanded')">
                <div class="indicator-summary">
                    <span class="indicator-icon-left">📻</span>
                    <div class="indicator-main">
                        <span class="indicator-label">Ruido HF</span>
                        <span class="indicator-value"><?php echo $data['signalnoise']; ?></span>
                        <span class="indicator-status">S-Units</span>
                    </div>
                    <span class="indicator-status-dot"></span>
                    <span class="expand-arrow">▼</span>
                </div>
                <div class="indicator-details">
                    <div class="indicator-details-content">
                        <div class="indicator-description"><strong>Ruido de Señal:</strong> Nivel de ruido atmosférico en bandas HF. Valores bajos (S0-S2) permiten escuchar señales más débiles. Alto ruido dificulta la recepción.</div>
                        <div class="indicator-scale">S0-S9 (bajo mejor para RX)</div>
                        <div class="indicator-extended"><strong>Operativa:</strong> S>5 enmascara señales débiles. Imposible copiar estaciones QRP. Mejor usar modos digitales (FT8) que resisten ruido.<br><strong>Salud:</strong> Ruido atmosférico natural, sin impacto en salud. No confundir con ruido eléctrico de fuentes artificiales (líneas alta tensión, etc).</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>


            
            <div class="disclaimer">
                ⚠️ <strong>Disclaimer:</strong> Los datos presentados son obtenidos de fuentes externas como HamQSL y NOAA. Aunque se hace todo lo posible por asegurar su precisión, no se garantiza la exactitud ni la actualidad de la información. Los usuarios deben verificar los datos críticos antes de tomar decisiones operativas basadas en ellos. El sitio no se responsabiliza por daños o pérdidas derivadas del uso de esta información.
            </div>
        </div>

        <button class="refresh-btn" onclick="location.reload()">
            🔄 Actualizar Datos
        </button>
        
        <script>
            // Auto-refresh cada 5 minutos
            setTimeout(function() {
                location.reload();
            }, 300000);
        </script>
    </div>
</body>
</html>