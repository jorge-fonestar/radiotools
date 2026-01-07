<?php
require_once 'nav.php';

/**
 * Clase para procesar imágenes de waterfall SDR y convertirlas a audio
 * Implementación PHP equivalente al script Python usando scipy/opencv
 */
class WaterfallProcessor {
    private $waterfallDuration; // Duración real del waterfall en segundos
    private $filterCutoff;
    private $outputSampleRate; // Sample rate de salida del WAV

    public function __construct($waterfallDuration = 5.0, $filterCutoff = 0.2, $outputSampleRate = 8000) {
        $this->waterfallDuration = $waterfallDuration;
        $this->filterCutoff = $filterCutoff;
        $this->outputSampleRate = $outputSampleRate;
    }

    /**
     * Procesa una imagen de waterfall y genera un archivo WAV
     * @param string $imagePath Ruta a la imagen del waterfall
     * @return array ['success' => bool, 'message' => string, 'wavPath' => string]
     */
    public function processWaterfall($imagePath) {
        // 1. Validar que el archivo existe
        if (!file_exists($imagePath)) {
            return ['success' => false, 'message' => 'Archivo no encontrado'];
        }

        // 2. Cargar imagen y convertir a escala de grises
        $imageInfo = getimagesize($imagePath);
        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $img = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($imagePath);
                break;
            default:
                return ['success' => false, 'message' => 'Formato de imagen no soportado'];
        }

        if (!$img) {
            return ['success' => false, 'message' => 'Error al cargar la imagen'];
        }

        // 3. Obtener dimensiones
        $width = imagesx($img);
        $height = imagesy($img);

        // 4. Convertir a matriz de grises
        $grayMatrix = $this->imageToGrayscale($img, $width, $height);
        imagedestroy($img);

        // 5. Demodular usando centro de masa
        $demodulated = $this->demodulateByMassCenter($grayMatrix, $width, $height);

        // 6. Procesamiento DSP
        $audioWave = $this->processDSP($demodulated);

        // 7. Resamplear para que coincida con la duración real del waterfall
        // La imagen tiene $height píxeles que representan $waterfallDuration segundos
        // Necesitamos generar outputSampleRate * waterfallDuration muestras
        $targetSamples = (int)($this->outputSampleRate * $this->waterfallDuration);
        $resampled = $this->resampleAudio($audioWave, $targetSamples);

        // 8. Normalizar a 16-bit PCM
        $audioInt16 = $this->normalizeToInt16($resampled);

        // 9. Generar archivo WAV
        $wavPath = 'uploads/waterfall_' . time() . '.wav';
        $this->generateWAV($wavPath, $audioInt16);

        return [
            'success' => true,
            'message' => 'Audio generado exitosamente',
            'wavPath' => $wavPath,
            'duration' => round(count($audioInt16) / $this->outputSampleRate, 2),
            'samples' => count($audioInt16),
            'sampleRate' => $this->outputSampleRate,
            'imageHeight' => $height,
            'waterfallDuration' => $this->waterfallDuration,
            'resampleRatio' => round($targetSamples / count($audioWave), 2)
        ];
    }

    /**
     * Convierte imagen RGB a matriz de intensidades en escala de grises
     */
    private function imageToGrayscale($img, $width, $height) {
        $matrix = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                // Conversión estándar a escala de grises
                $gray = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                $row[] = $gray;
            }
            $matrix[] = $row;
        }
        return $matrix;
    }

    /**
     * Demodulación por centro de masa (más preciso que argmax)
     * Calcula el promedio ponderado de intensidad en cada fila
     */
    private function demodulateByMassCenter($grayMatrix, $width, $height) {
        $demodulated = [];

        for ($y = 0; $y < $height; $y++) {
            $row = $grayMatrix[$y];
            $intensitySum = array_sum($row);

            if ($intensitySum > 0) {
                // Centro de masa: sum(x_i * intensity_i) / sum(intensity_i)
                $weightedSum = 0;
                for ($x = 0; $x < $width; $x++) {
                    $weightedSum += $x * $row[$x];
                }
                $center = $weightedSum / $intensitySum;
                $demodulated[] = $center;
            } else {
                // Si la fila está vacía, usar el centro
                $demodulated[] = $width / 2;
            }
        }

        return $demodulated;
    }

    /**
     * Procesamiento de señal digital (DSP)
     * - Elimina componente DC (portadora)
     * - Aplica filtro paso-bajo Butterworth
     */
    private function processDSP($signal) {
        // Eliminar componente DC
        $mean = array_sum($signal) / count($signal);
        $audioWave = array_map(function($val) use ($mean) {
            return $val - $mean;
        }, $signal);

        // Aplicar filtro paso-bajo Butterworth de orden 4
        $audioWave = $this->butterworthLowPass($audioWave, $this->filterCutoff);

        return $audioWave;
    }

    /**
     * Implementación de filtro Butterworth paso-bajo de orden 4
     * Usa método filtfilt (filtrado bidireccional) para fase cero
     */
    private function butterworthLowPass($signal, $cutoff) {
        // Coeficientes del filtro Butterworth digital de orden 4
        // Calculados para cutoff normalizado (0.2 = 20% de Nyquist)
        $coeffs = $this->butterworthCoefficients(4, $cutoff);

        // Filtrar hacia adelante
        $forward = $this->applyIIRFilter($signal, $coeffs['b'], $coeffs['a']);

        // Filtrar hacia atrás (filtfilt para fase cero)
        $reversed = array_reverse($forward);
        $backward = $this->applyIIRFilter($reversed, $coeffs['b'], $coeffs['a']);

        return array_reverse($backward);
    }

    /**
     * Calcula coeficientes del filtro Butterworth digital
     */
    private function butterworthCoefficients($order, $cutoff) {
        // Para orden 4, cutoff 0.2, coeficientes aproximados
        // En producción, usar transformación bilineal completa
        // Estos valores son una aproximación para cutoff=0.2

        if ($order == 4 && abs($cutoff - 0.2) < 0.01) {
            return [
                'b' => [0.0048, 0.0193, 0.0289, 0.0193, 0.0048],
                'a' => [1.0000, -2.3695, 2.3140, -1.0547, 0.1874]
            ];
        }

        // Aproximación simple para otros valores
        $omega = tan(M_PI * $cutoff);
        $omega2 = $omega * $omega;
        $omega3 = $omega2 * $omega;
        $omega4 = $omega2 * $omega2;

        $k = 1 / (1 + 2.613 * $omega + 3.414 * $omega2 + 2.613 * $omega3 + $omega4);

        return [
            'b' => [
                $k * $omega4,
                $k * 4 * $omega4,
                $k * 6 * $omega4,
                $k * 4 * $omega4,
                $k * $omega4
            ],
            'a' => [
                1.0,
                $k * (4 * $omega4 - 2.613 * $omega - 6.828 * $omega2 - 2.613 * $omega3),
                $k * (6 * $omega4 + 3.414 * $omega2),
                $k * (4 * $omega4 + 2.613 * $omega + 6.828 * $omega2 - 2.613 * $omega3),
                $k * ($omega4 - 2.613 * $omega + 3.414 * $omega2 + 2.613 * $omega3)
            ]
        ];
    }

    /**
     * Aplica filtro IIR (Infinite Impulse Response)
     */
    private function applyIIRFilter($signal, $b, $a) {
        $n = count($signal);
        $m = count($b);
        $filtered = array_fill(0, $n, 0);

        for ($i = 0; $i < $n; $i++) {
            $y = 0;

            // Parte feedforward (coeficientes b)
            for ($j = 0; $j < $m; $j++) {
                if ($i - $j >= 0) {
                    $y += $b[$j] * $signal[$i - $j];
                }
            }

            // Parte feedback (coeficientes a)
            for ($j = 1; $j < count($a); $j++) {
                if ($i - $j >= 0) {
                    $y -= $a[$j] * $filtered[$i - $j];
                }
            }

            $filtered[$i] = $y;
        }

        return $filtered;
    }

    /**
     * Resampling usando interpolación lineal
     * Cambia el número de muestras de la señal de audio
     * @param array $signal Señal original
     * @param int $targetSamples Número de muestras deseadas
     * @return array Señal resampleada
     */
    private function resampleAudio($signal, $targetSamples) {
        $sourceSamples = count($signal);

        if ($sourceSamples == $targetSamples) {
            return $signal;
        }

        $resampled = [];
        $ratio = ($sourceSamples - 1) / ($targetSamples - 1);

        for ($i = 0; $i < $targetSamples; $i++) {
            $sourceIndex = $i * $ratio;
            $indexFloor = (int)floor($sourceIndex);
            $indexCeil = min($indexFloor + 1, $sourceSamples - 1);
            $fraction = $sourceIndex - $indexFloor;

            // Interpolación lineal
            $resampled[] = $signal[$indexFloor] * (1 - $fraction) +
                          $signal[$indexCeil] * $fraction;
        }

        return $resampled;
    }

    /**
     * Normaliza señal de audio a formato 16-bit PCM
     */
    private function normalizeToInt16($signal) {
        // Encontrar valor absoluto máximo
        $maxAbs = 0;
        foreach ($signal as $val) {
            $abs = abs($val);
            if ($abs > $maxAbs) {
                $maxAbs = $abs;
            }
        }

        // Normalizar y convertir a int16
        $int16 = [];
        if ($maxAbs > 0) {
            foreach ($signal as $val) {
                $normalized = $val / $maxAbs;
                $int16[] = (int)($normalized * 32767);
            }
        } else {
            $int16 = array_fill(0, count($signal), 0);
        }

        return $int16;
    }

    /**
     * Genera archivo WAV desde datos PCM 16-bit
     * Formato: RIFF WAV, mono, 16-bit PCM
     */
    private function generateWAV($filename, $audioData) {
        $numSamples = count($audioData);
        $numChannels = 1; // Mono
        $bitsPerSample = 16;
        $byteRate = $this->outputSampleRate * $numChannels * ($bitsPerSample / 8);
        $blockAlign = $numChannels * ($bitsPerSample / 8);
        $dataSize = $numSamples * $blockAlign;

        // Crear directorio si no existe
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fp = fopen($filename, 'wb');

        // RIFF header
        fwrite($fp, 'RIFF');
        fwrite($fp, pack('V', $dataSize + 36)); // ChunkSize
        fwrite($fp, 'WAVE');

        // fmt subchunk
        fwrite($fp, 'fmt ');
        fwrite($fp, pack('V', 16)); // Subchunk1Size (16 for PCM)
        fwrite($fp, pack('v', 1)); // AudioFormat (1 = PCM)
        fwrite($fp, pack('v', $numChannels));
        fwrite($fp, pack('V', $this->outputSampleRate));
        fwrite($fp, pack('V', $byteRate));
        fwrite($fp, pack('v', $blockAlign));
        fwrite($fp, pack('v', $bitsPerSample));

        // data subchunk
        fwrite($fp, 'data');
        fwrite($fp, pack('V', $dataSize));

        // Audio data
        foreach ($audioData as $sample) {
            fwrite($fp, pack('s', $sample)); // signed short
        }

        fclose($fp);
    }
}

// Procesamiento del formulario
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['waterfall_image'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadedFile = $_FILES['waterfall_image'];
    $tempPath = $uploadedFile['tmp_name'];

    // Obtener parámetros
    $waterfallDuration = isset($_POST['waterfall_duration']) ? (float)$_POST['waterfall_duration'] : 5.0;
    $filterCutoff = isset($_POST['filter_cutoff']) ? (float)$_POST['filter_cutoff'] : 0.2;
    $outputSampleRate = isset($_POST['output_sample_rate']) ? (int)$_POST['output_sample_rate'] : 8000;

    // Validar parámetros
    $waterfallDuration = max(0.1, min(300, $waterfallDuration)); // 0.1s a 5 minutos
    $filterCutoff = max(0.01, min(0.49, $filterCutoff));
    $outputSampleRate = max(1000, min(48000, $outputSampleRate));

    // Procesar
    $processor = new WaterfallProcessor($waterfallDuration, $filterCutoff, $outputSampleRate);
    $result = $processor->processWaterfall($tempPath);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decodificador Waterfall - RadioTools</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .waterfall-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .upload-section {
            background: #1a1a1a;
            border: 2px dashed #333;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }

        .upload-section:hover {
            border-color: #4CAF50;
        }

        .upload-section.drag-over {
            border-color: #4CAF50;
            background: #1f2f1f;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            margin: 10px 0;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .file-input-label:hover {
            background: #45a049;
        }

        .preview-section {
            margin: 20px 0;
            display: none;
        }

        .preview-section.active {
            display: block;
        }

        .preview-image {
            max-width: 100%;
            border-radius: 8px;
            border: 1px solid #333;
        }

        .param-group {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .param-item {
            margin: 15px 0;
        }

        .param-item label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
            font-weight: 500;
        }

        .param-item input[type=range] {
            width: 100%;
            margin: 10px 0;
        }

        .param-item input[type=number] {
            width: 100%;
            padding: 10px;
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 6px;
            color: white;
            font-size: 16px;
        }

        .param-value {
            display: inline-block;
            background: #0a0a0a;
            padding: 4px 12px;
            border-radius: 4px;
            font-family: monospace;
            color: #4CAF50;
        }

        .process-btn {
            width: 100%;
            padding: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .process-btn:hover {
            background: #45a049;
        }

        .process-btn:disabled {
            background: #555;
            cursor: not-allowed;
        }

        .result-section {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .result-success {
            border-left: 4px solid #4CAF50;
        }

        .result-error {
            border-left: 4px solid #F44336;
        }

        .audio-player {
            width: 100%;
            margin: 15px 0;
        }

        .download-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .download-btn:hover {
            background: #1976D2;
        }

        .info-box {
            background: #0a0a0a;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }

        .info-box h4 {
            margin-top: 0;
            color: #4CAF50;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #222;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #999;
        }

        .info-value {
            color: white;
            font-weight: bold;
        }

        .help-text {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php renderNavMenu('waterfall.php'); ?>

    <div class="waterfall-container">
        <h1>🌊 Decodificador Waterfall</h1>
        <p style="color: #999;">
            Convierte imágenes de waterfall SDR a audio. Sube una captura de pantalla
            del espectro de tu SDR y genera un archivo WAV con el audio demodulado.
        </p>

        <form method="POST" enctype="multipart/form-data" id="waterfall-form">
            <div class="upload-section" id="upload-zone">
                <div style="font-size: 48px; margin-bottom: 10px;">📸</div>
                <h3>Selecciona una imagen de waterfall</h3>
                <p style="color: #999;">JPG, PNG o GIF</p>

                <div class="file-input-wrapper">
                    <input type="file" name="waterfall_image" id="waterfall-input"
                           accept="image/jpeg,image/png,image/gif" required>
                    <label for="waterfall-input" class="file-input-label">
                        Elegir archivo
                    </label>
                </div>

                <p class="help-text" id="filename-display">O arrastra un archivo aquí</p>
            </div>

            <div class="preview-section" id="preview-section">
                <h3>Vista previa</h3>
                <img id="preview-img" class="preview-image" alt="Vista previa">
            </div>

            <div class="param-group">
                <h3>⚙️ Parámetros de procesamiento</h3>

                <div class="param-item">
                    <label for="waterfall-duration">
                        ⏱️ Duración del waterfall: <span class="param-value" id="duration-value">5.0</span> segundos
                    </label>
                    <input type="range" id="waterfall-duration" name="waterfall_duration"
                           min="0.5" max="60" step="0.5" value="5.0">
                    <p class="help-text">
                        <strong>IMPORTANTE:</strong> Tiempo real que representa la imagen del waterfall.
                        Por ejemplo, si capturaste 5 segundos de señal, usa 5.0 segundos.
                        Este parámetro es crítico para la correcta velocidad del audio.
                    </p>
                </div>

                <div class="param-item">
                    <label for="output-sample-rate">
                        Calidad de audio: <span class="param-value" id="sample-rate-value">8000</span> Hz
                    </label>
                    <input type="range" id="output-sample-rate" name="output_sample_rate"
                           min="4000" max="48000" step="1000" value="8000">
                    <p class="help-text">
                        Sample rate del archivo WAV generado. Mayor = mejor calidad pero archivo más grande.
                        Recomendado: 8000 Hz (voz), 16000 Hz (datos), 44100 Hz (alta calidad).
                    </p>
                </div>

                <div class="param-item">
                    <label for="filter-cutoff">
                        Frecuencia de corte del filtro: <span class="param-value" id="filter-value">0.20</span>
                    </label>
                    <input type="range" id="filter-cutoff" name="filter_cutoff"
                           min="0.05" max="0.45" step="0.01" value="0.20">
                    <p class="help-text">
                        Filtro paso-bajo para eliminar ruido. Valores bajos = más suave,
                        valores altos = más detalle. Recomendado: 0.15-0.25.
                    </p>
                </div>
            </div>

            <button type="submit" class="process-btn" id="process-btn" disabled>
                🎵 Generar Audio
            </button>
        </form>

        <?php if ($result): ?>
        <div class="result-section <?php echo $result['success'] ? 'result-success' : 'result-error'; ?>">
            <?php if ($result['success']): ?>
                <h3 style="color: #4CAF50;">✅ Audio generado exitosamente</h3>

                <div class="info-box">
                    <h4>Información del archivo</h4>
                    <div class="info-item">
                        <span class="info-label">Duración waterfall:</span>
                        <span class="info-value"><?php echo $result['waterfallDuration']; ?> segundos</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Duración audio:</span>
                        <span class="info-value"><?php echo $result['duration']; ?> segundos</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Altura imagen:</span>
                        <span class="info-value"><?php echo $result['imageHeight']; ?> píxeles</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Muestras generadas:</span>
                        <span class="info-value"><?php echo number_format($result['samples']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sample rate:</span>
                        <span class="info-value"><?php echo $result['sampleRate']; ?> Hz</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ratio resample:</span>
                        <span class="info-value"><?php echo $result['resampleRatio']; ?>x</span>
                    </div>
                </div>

                <audio controls class="audio-player">
                    <source src="<?php echo $result['wavPath']; ?>" type="audio/wav">
                    Tu navegador no soporta el reproductor de audio.
                </audio>

                <a href="<?php echo $result['wavPath']; ?>" download class="download-btn">
                    ⬇️ Descargar WAV
                </a>
            <?php else: ?>
                <h3 style="color: #F44336;">❌ Error al procesar</h3>
                <p><?php echo htmlspecialchars($result['message']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="info-box" style="margin-top: 30px;">
            <h4>ℹ️ ¿Cómo funciona?</h4>
            <ol style="color: #ccc; line-height: 1.8;">
                <li><strong>Carga de imagen:</strong> Se analiza cada píxel del waterfall en escala de grises.</li>
                <li><strong>Demodulación por centro de masa:</strong> Para cada línea horizontal, se calcula el centro de energía (más preciso que solo el pico).</li>
                <li><strong>Resampling temporal:</strong> La señal se reescala para que coincida con la duración real del waterfall que capturaste.</li>
                <li><strong>Filtrado DSP:</strong> Se elimina la portadora DC y se aplica un filtro Butterworth paso-bajo para eliminar ruido de pixelación.</li>
                <li><strong>Normalización:</strong> Se convierte a formato PCM 16-bit estándar.</li>
                <li><strong>Generación WAV:</strong> Se crea un archivo de audio compatible con cualquier reproductor.</li>
            </ol>

            <h4 style="margin-top: 20px;">💡 Consejos</h4>
            <ul style="color: #ccc; line-height: 1.8;">
                <li><strong>Duración del waterfall:</strong> Verifica cuántos segundos abarca tu captura en el SDR. Este es el parámetro MÁS IMPORTANTE para obtener la velocidad correcta del audio.</li>
                <li>Usa capturas limpias del waterfall con buena relación señal/ruido</li>
                <li>Para señales de voz (SSB, AM): usa 8000 Hz de sample rate</li>
                <li>Para modos digitales (PSK, RTTY, FT8): usa 8000-16000 Hz</li>
                <li>Para alta fidelidad o análisis: usa 44100 Hz</li>
                <li>Si el audio tiene "clics" o ruido de pixelación, reduce la frecuencia de corte del filtro (0.10-0.15)</li>
                <li>Si necesitas más detalle en señales rápidas, aumenta la frecuencia de corte del filtro (0.30-0.40)</li>
                <li>La duración del audio generado SIEMPRE será igual a la duración del waterfall que especificaste</li>
            </ul>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('waterfall-input');
        const uploadZone = document.getElementById('upload-zone');
        const previewSection = document.getElementById('preview-section');
        const previewImg = document.getElementById('preview-img');
        const processBtn = document.getElementById('process-btn');
        const filenameDisplay = document.getElementById('filename-display');
        const durationSlider = document.getElementById('waterfall-duration');
        const durationValue = document.getElementById('duration-value');
        const sampleRateSlider = document.getElementById('output-sample-rate');
        const sampleRateValue = document.getElementById('sample-rate-value');
        const filterSlider = document.getElementById('filter-cutoff');
        const filterValue = document.getElementById('filter-value');

        // Actualizar valores de sliders
        durationSlider.addEventListener('input', function() {
            durationValue.textContent = parseFloat(this.value).toFixed(1);
        });

        sampleRateSlider.addEventListener('input', function() {
            sampleRateValue.textContent = this.value;
        });

        filterSlider.addEventListener('input', function() {
            filterValue.textContent = parseFloat(this.value).toFixed(2);
        });

        // Preview de imagen
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                filenameDisplay.textContent = file.name;

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewSection.classList.add('active');
                    processBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        uploadZone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });

        // Mostrar estado de procesamiento
        document.getElementById('waterfall-form').addEventListener('submit', function() {
            processBtn.textContent = '⏳ Procesando...';
            processBtn.disabled = true;
        });
    </script>
</body>
</html>
