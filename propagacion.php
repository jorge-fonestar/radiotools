<?php
require_once 'nav.php';

// Función para obtener datos solares (reutilizada del solar.php)
function getSolarData() {
    $cache_file = 'solar_cache.json';
    $cache_time = 300; // 5 minutos

    // Verificar cache
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $cached = file_get_contents($cache_file);
        $xml = simplexml_load_string($cached);
        if ($xml) {
            return xmlToArrayPropagation($xml);
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
        file_put_contents($cache_file, $response);
        $xml = simplexml_load_string($response);
        if ($xml) {
            return xmlToArrayPropagation($xml);
        }
    }

    return false;
}

function xmlToArrayPropagation($xml) {
    $solar = $xml->solardata;
    return [
        'solarflux' => (int)$solar->solarflux,
        'sunspots' => (int)$solar->sunspots,
        'kindex' => (int)$solar->kindex,
        'aindex' => (int)$solar->aindex,
        'xray' => (string)$solar->xray,
        'updated' => (string)$solar->updated
    ];
}

// Modelo heurístico de propagación
class PropagationCalculator {
    private $solarData;
    private $qthLat;
    private $qthLon;
    private $utcTime;
    
    public function __construct($qthLat, $qthLon, $solarData, $utcTime = null) {
        $this->qthLat = $qthLat;
        $this->qthLon = $qthLon;
        $this->solarData = $solarData;
        $this->utcTime = $utcTime ?: time();
    }
    
    // Calcular MUF aproximada basada en SFI
    private function calculateMUF($sfi, $distance) {
        // Fórmula mejorada basada en datos reales
        // foF2 crítica depende del SFI
        $foF2 = 2.5 + ($sfi - 70) * 0.025; // MHz, más realista
        $foF2 = max(2.0, min(15.0, $foF2)); // Limitar entre valores razonables
        
        // Factor de distancia mejorado
        $distanceKm = $distance;
        if ($distanceKm < 300) {
            // NVIS - Near Vertical Incidence Skywave
            $muf = $foF2 * 0.85;
        } else {
            // Skip largo - factor M basado en distancia
            $mFactor = 1.0 + ($distanceKm - 300) / 3000; // Incremento gradual
            $mFactor = min(4.0, $mFactor); // Máximo factor 4
            $muf = $foF2 * $mFactor;
        }
        
        return $muf;
    }
    
    // Calcular LUF aproximada
    private function calculateLUF($distance, $absorption) {
        // LUF mejorada - más realista
        $baseLUF = 1.5; // MHz base
        
        // Factor de distancia más suave
        if ($distance < 500) {
            $distanceFactor = 0; // Distancias cortas, LUF baja
        } else {
            $distanceFactor = ($distance - 500) / 5000; // Incremento gradual
        }
        
        // Factor de absorción reducido
        $absorptionFactor = $absorption * 0.5; // Menos penalización
        
        $luf = $baseLUF + $distanceFactor + $absorptionFactor;
        return max(1.0, min(10.0, $luf)); // Limitar entre 1-10 MHz
    }
    
    // Calcular absorción D-layer
    private function calculateAbsorption($zenithAngle, $xrayClass) {
        $absorption = 0;
        
        // Absorción básica por ángulo solar (más suave)
        if ($zenithAngle < 90) { // Día
            $absorption = (90 - $zenithAngle) / 180; // Reducido a la mitad
        }
        
        // Absorción por rayos X (menos agresiva)
        $xrayFactor = 0;
        if (strlen($xrayClass) > 0) {
            $class = substr($xrayClass, 0, 1);
            switch($class) {
                case 'A': $xrayFactor = 0; break;
                case 'B': $xrayFactor = 0.05; break;
                case 'C': $xrayFactor = 0.15; break;
                case 'M': $xrayFactor = 0.3; break;
                case 'X': $xrayFactor = 0.5; break;
            }
        }
        
        return $absorption + $xrayFactor;
    }
    
    // Calcular ángulo cenital solar
    private function calculateSolarZenithAngle($lat, $lon, $timestamp) {
        $dayOfYear = date('z', $timestamp) + 1;
        $hour = date('H', $timestamp) + date('i', $timestamp) / 60;
        
        // Declinación solar
        $declination = 23.45 * sin(deg2rad(360 * (284 + $dayOfYear) / 365));
        
        // Ángulo horario
        $hourAngle = 15 * ($hour - 12) + $lon;
        
        // Ángulo cenital
        $zenith = acos(
            sin(deg2rad($lat)) * sin(deg2rad($declination)) +
            cos(deg2rad($lat)) * cos(deg2rad($declination)) * cos(deg2rad($hourAngle))
        );
        
        return rad2deg($zenith);
    }
    
    // Calcular distancia entre dos puntos
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $R = 6371; // Radio de la Tierra en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $R * $c;
    }
    
    // Evaluar propagación para una banda específica
    public function evaluateBand($frequency, $destLat, $destLon) {
        $distance = $this->calculateDistance($this->qthLat, $this->qthLon, $destLat, $destLon);
        
        // Ángulo cenital solar en destino
        $zenithAngle = $this->calculateSolarZenithAngle($destLat, $destLon, $this->utcTime);
        
        // Calcular parámetros de propagación
        $absorption = $this->calculateAbsorption($zenithAngle, $this->solarData['xray']);
        $muf = $this->calculateMUF($this->solarData['solarflux'], $distance);
        $luf = $this->calculateLUF($distance, $absorption);
        
        // Debug: agregar información para depuración
        $isOpen = ($frequency >= $luf && $frequency <= $muf);
        
        // Calcular probabilidad base
        $probability = 0;
        if ($isOpen) {
            // Método mejorado para calcular probabilidad
            $range = $muf - $luf;
            if ($range > 0) {
                // Probabilidad base dependiendo de dónde está la frecuencia en el rango
                if ($frequency <= ($luf + $range * 0.2)) {
                    // Cerca del LUF - probabilidad baja
                    $probability = 0.3;
                } elseif ($frequency >= ($muf - $range * 0.2)) {
                    // Cerca del MUF - probabilidad media
                    $probability = 0.6;
                } else {
                    // En el centro del rango - probabilidad alta
                    $probability = 0.8;
                }
            }
        } else {
            // Banda cerrada, pero dar pequeña probabilidad si está cerca
            if ($frequency < $luf && ($luf - $frequency) < 2.0) {
                $probability = 0.1; // Pequeña probabilidad
            } elseif ($frequency > $muf && ($frequency - $muf) < 3.0) {
                $probability = 0.15; // Pequeña probabilidad
            }
        }
        
        // Aplicar modificadores
        if ($probability > 0) {
            // Penalizar por actividad geomagnética (menos agresivo)
            if ($this->solarData['kindex'] > 4) {
                $kPenalty = 1 - (($this->solarData['kindex'] - 4) * 0.1);
                $probability *= max(0.3, $kPenalty); // Mínimo 30%
            }
            
            // Bonificar línea gris (terminador solar)
            if ($zenithAngle >= 85 && $zenithAngle <= 95) {
                $probability *= 1.2; // Bonificación 20%
            }
            
            // Bonificar condiciones nocturnas en destino
            if ($zenithAngle > 105) {
                $probability *= 1.1; // Bonificación nocturna 10%
            }
            
            // Penalizar rutas polares con alta actividad geomagnética
            if (abs($destLat) > 60 && $this->solarData['kindex'] > 3) {
                $probability *= 0.7;
            }
            
            // Bonificar por buen SFI
            if ($this->solarData['solarflux'] > 120) {
                $probability *= 1.1;
            }
        }
        
        return [
            'probability' => min(1, max(0, $probability)),
            'muf' => round($muf, 1),
            'luf' => round($luf, 1),
            'distance' => round($distance),
            'zenith' => round($zenithAngle, 1),
            'isOpen' => $isOpen,
            'absorption' => round($absorption, 2)
        ];
    }
    
    // Obtener las mejores regiones para una banda
    public function getBestRegions($frequency) {
        $regions = [
            'Europa' => [50, 10],
            'África' => [0, 20],
            'Asia' => [35, 100],
            'Oceanía' => [-25, 140],
            'Norteamérica' => [40, -100],
            'Sudamérica' => [-15, -60],
            'Caribe' => [15, -70],
            'Oriente Medio' => [30, 45],
            'Japón' => [35, 140],
            'Rusia' => [60, 60]
        ];
        
        $results = [];
        foreach ($regions as $name => $coords) {
            $result = $this->evaluateBand($frequency, $coords[0], $coords[1]);
            $result['region'] = $name;
            $results[] = $result;
        }
        
        // Ordenar por probabilidad
        usort($results, function($a, $b) {
            return $b['probability'] <=> $a['probability'];
        });
        
        return $results;
    }
}

// Procesar formulario
$results = null;
$solarData = getSolarData();
$error = null;

if ($_POST && isset($_POST['calculate'])) {
    try {
        $qthLat = floatval($_POST['qth_lat']);
        $qthLon = floatval($_POST['qth_lon']);
        $selectedBands = $_POST['bands'] ?? [];
        
        if (empty($selectedBands)) {
            throw new Exception("Selecciona al menos una banda");
        }
        
        $bandFreqs = [
            '160m' => 1.8,
            '80m' => 3.5,
            '40m' => 7.0,
            '30m' => 10.1,
            '20m' => 14.0,
            '17m' => 18.1,
            '15m' => 21.0,
            '12m' => 24.9,
            '10m' => 28.0
        ];
        
        $calculator = new PropagationCalculator($qthLat, $qthLon, $solarData);
        $results = [];
        
        foreach ($selectedBands as $band) {
            if (isset($bandFreqs[$band])) {
                $frequency = $bandFreqs[$band];
                $bandResults = $calculator->getBestRegions($frequency);
                $results[$band] = [
                    'frequency' => $frequency,
                    'regions' => $bandResults
                ];
            }
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Calculador de Propagación HF</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 600px;
        }
        
        .wide-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .results-container {
            display: grid;
            gap: 20px;
        }
        
        .region-item {
            background: #2a2a2a;
            border-radius: 6px;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .probability-bar {
            height: 4px;
            background: #333;
            border-radius: 2px;
            overflow: hidden;
            flex: 1;
            margin: 0 10px;
        }
        
        .probability-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .region-name {
            min-width: 100px;
            font-size: 12px;
            color: #ccc;
        }
        
        .probability-value {
            font-size: 11px;
            color: #888;
            min-width: 40px;
            text-align: right;
        }
        
        .location-btn {
            background: #2a2a2a;
            border: 1px solid #444;
            color: #ccc;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .location-btn:hover {
            background: #333;
            border-color: #4CAF50;
            color: #4CAF50;
        }
        
        .location-btn:active {
            transform: scale(0.95);
        }
        
        .select-all-btn {
            background: #2a2a2a;
            border: 1px solid #444;
            color: #ccc;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 8px;
            margin-bottom: 5px;
        }
        
        .select-all-btn:hover {
            background: #333;
            border-color: #4CAF50;
            color: #4CAF50;
        }
        
        .select-all-btn:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php renderNavMenu('propagacion.php'); ?>
        
        <div class="header">
            <h1>📡 Calculador Propagación HF</h1>
            <div class="update-time">
                Datos solares: <?php echo $solarData ? $solarData['updated'] : 'No disponibles'; ?>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="error">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Datos solares actuales -->
        <?php if ($solarData): ?>
        <div class="bands-section">
            <div class="bands-header" onclick="this.classList.parent.classList.toggle('expanded')">
                <h2 class="bands-title">☀️ Condiciones Solares Actuales</h2>
                <span class="bands-expand-arrow">▼</span>
            </div>
            <div class="bands-content">
                <div style="padding: 15px;">
                    <div class="band-grid">
                        <div class="band-item" style="--band-color: #4CAF50;">
                            <div class="band-name">SFI</div>
                            <div class="band-status"><?php echo $solarData['solarflux']; ?></div>
                        </div>
                        <div class="band-item" style="--band-color: #FF9800;">
                            <div class="band-name">K-Index</div>
                            <div class="band-status"><?php echo $solarData['kindex']; ?></div>
                        </div>
                        <div class="band-item" style="--band-color: #2196F3;">
                            <div class="band-name">A-Index</div>
                            <div class="band-status"><?php echo $solarData['aindex']; ?></div>
                        </div>
                        <div class="band-item" style="--band-color: #9C27B0;">
                            <div class="band-name">Rayos X</div>
                            <div class="band-status"><?php echo $solarData['xray']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" action="">
            <div class="form-section">
                <h2 class="form-title">📍 Configuración QTH</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="qth_lat">Latitud</label>
                        <input type="number" 
                               step="0.000001" 
                               id="qth_lat" 
                               name="qth_lat" 
                               class="form-input" 
                               placeholder="40.123456"
                               value="<?php echo isset($_POST['qth_lat']) ? htmlspecialchars($_POST['qth_lat']) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="qth_lon">Longitud</label>
                        <input type="number" 
                               step="0.000001" 
                               id="qth_lon" 
                               name="qth_lon" 
                               class="form-input" 
                               placeholder="-3.123456"
                               value="<?php echo isset($_POST['qth_lon']) ? htmlspecialchars($_POST['qth_lon']) : ''; ?>"
                               required>
                    </div>
                </div>
                
                <div style="margin-top: 10px;">
                    <label class="form-label">🎯 Ubicaciones Rápidas:</label>
                    <div class="checkbox-group" style="grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));">
                        <button type="button" class="location-btn" onclick="setLocation(43.4623, -3.8099, 'Santander')">
                            📍 Santander
                        </button>
                        <button type="button" class="location-btn" onclick="setLocation(43.3878, -4.2856, 'Comillas')">
                            🏖️ Comillas
                        </button>
                        <button type="button" class="location-btn" onclick="setLocation(40.4168, -3.7038, 'Madrid')">
                            🏛️ Madrid
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="form-title">📻 Bandas a Evaluar</h2>
                
                <div style="margin-bottom: 10px;">
                    <button type="button" class="select-all-btn" onclick="selectAllBands(true)">
                        ✅ Seleccionar Todas
                    </button>
                    <button type="button" class="select-all-btn" onclick="selectAllBands(false)">
                        ❌ Deseleccionar Todas
                    </button>
                </div>
                
                <div class="checkbox-group">
                    <?php 
                    $bands = ['160m', '80m', '40m', '30m', '20m', '17m', '15m', '12m', '10m'];
                    $selected = $_POST['bands'] ?? ['40m', '20m', '10m']; // Bandas por defecto
                    
                    foreach ($bands as $band): 
                    ?>
                    <div class="checkbox-item">
                        <input type="checkbox" 
                               id="band_<?php echo $band; ?>" 
                               name="bands[]" 
                               value="<?php echo $band; ?>"
                               <?php echo in_array($band, $selected) ? 'checked' : ''; ?>>
                        <label for="band_<?php echo $band; ?>"><?php echo $band; ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" name="calculate" class="calculate-btn">
                🔄 Calcular Propagación
            </button>
        </form>

        <!-- Resultados -->
        <?php if ($results): ?>
        <div class="wide-container">
            <div class="results-container">
                <?php foreach ($results as $band => $data): ?>
                <div class="result-band" style="--band-color: #4CAF50;">
                    <div class="result-band-name">
                        <?php echo $band; ?> (<?php echo $data['frequency']; ?> MHz)
                    </div>
                    
                    <div class="result-description">
                        <?php 
                        $bestRegions = array_filter($data['regions'], function($r) { 
                            return $r['probability'] > 0.1; 
                        });
                        $bestRegions = array_slice($bestRegions, 0, 3);
                        
                        if (!empty($bestRegions)) {
                            $bestNames = array_map(function($r) { 
                                return $r['region'] . ' (' . round($r['probability'] * 100) . '%)'; 
                            }, $bestRegions);
                            echo "Mejores rutas: " . implode(', ', $bestNames);
                            
                            // Mostrar información técnica del primer resultado
                            $first = $data['regions'][0];
                            echo "<br><small style='color: #666;'>MUF: {$first['muf']} MHz | LUF: {$first['luf']} MHz | Dist: {$first['distance']} km</small>";
                        } else {
                            echo "Banda cerrada - Condiciones desfavorables";
                            // Mostrar por qué está cerrada
                            $first = $data['regions'][0];
                            echo "<br><small style='color: #666;'>Freq: {$data['frequency']} MHz fuera del rango {$first['luf']}-{$first['muf']} MHz</small>";
                        }
                        ?>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <?php foreach ($data['regions'] as $region): ?>
                        <div class="region-item">
                            <div class="region-name">
                                <?php echo $region['region']; ?>
                                <small style="display: block; font-size: 9px; color: #555;">
                                    <?php echo round($region['distance']); ?>km | Z:<?php echo round($region['zenith']); ?>°
                                </small>
                            </div>
                            <div class="probability-bar">
                                <div class="probability-fill" 
                                     style="width: <?php echo ($region['probability'] * 100); ?>%; 
                                            background: <?php 
                                            if ($region['probability'] > 0.7) echo '#4CAF50';
                                            elseif ($region['probability'] > 0.4) echo '#FFC107';
                                            elseif ($region['probability'] > 0.1) echo '#FF9800';
                                            else echo '#F44336';
                                            ?>;">
                                </div>
                            </div>
                            <div class="probability-value">
                                <?php echo round($region['probability'] * 100); ?>%
                                <small style="display: block; font-size: 9px; color: #666;">
                                    <?php echo $region['luf']; ?>-<?php echo $region['muf']; ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="disclaimer">
            ⚠️ <strong>Disclaimer:</strong> Este calculador utiliza un modelo heurístico simplificado para las predicciones de propagación.
            Los resultados deben considerarse como referencia orientativa. Para análisis profesionales 
            de propagación, se recomienda utilizar software especializado como VOACAP o ITURHFProp.
        </div>
    </div>

    <script>
        // Expandir automáticamente la sección de datos solares al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const solarSection = document.querySelector('.bands-section');
            if (solarSection) {
                solarSection.addEventListener('click', function() {
                    this.classList.toggle('expanded');
                });
            }
        });
        
        // Función para establecer ubicación rápida
        function setLocation(lat, lon, name) {
            document.getElementById('qth_lat').value = lat;
            document.getElementById('qth_lon').value = lon;
            
            // Mostrar confirmación visual
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = '✅ ' + name;
            button.style.backgroundColor = '#4CAF50';
            button.style.borderColor = '#4CAF50';
            button.style.color = 'white';
            
            // Restaurar después de 2 segundos
            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = '';
                button.style.borderColor = '';
                button.style.color = '';
            }, 2000);
            
            // Enfocar el primer campo de banda para continuar
            const firstBand = document.querySelector('input[name="bands[]"]');
            if (firstBand) {
                firstBand.focus();
            }
        }
        
        // Función para seleccionar/deseleccionar todas las bandas
        function selectAllBands(select) {
            const checkboxes = document.querySelectorAll('input[name="bands[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = select;
            });
            
            // Mostrar confirmación visual
            const button = event.target;
            const originalText = button.textContent;
            const originalColor = button.style.backgroundColor;
            
            if (select) {
                button.textContent = '✅ Todas Seleccionadas';
                button.style.backgroundColor = '#4CAF50';
            } else {
                button.textContent = '❌ Todas Deseleccionadas';
                button.style.backgroundColor = '#F44336';
            }
            button.style.color = 'white';
            button.style.borderColor = button.style.backgroundColor;
            
            // Restaurar después de 1.5 segundos
            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = '';
                button.style.borderColor = '';
                button.style.color = '';
            }, 1500);
        }
    </script>
</body>
</html>