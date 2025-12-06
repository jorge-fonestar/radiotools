<?php
require_once 'nav.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gestor de QSO - RadioTools</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilos específicos para el gestor QSO */
        .qso-container {
            max-width: 500px;
            margin: 0 auto;
        }

        .qso-section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 14px;
            color: #888;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Cronómetro */
        .timer-container {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 12px 15px;
        }

        .timer-display {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            flex: 1;
        }

        .timer-btn {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            -webkit-tap-highlight-color: rgba(76, 175, 80, 0.1);
            touch-action: manipulation;
            white-space: nowrap;
        }

        .timer-btn:hover {
            background: #45a049;
        }

        .timer-btn:active {
            transform: scale(0.95);
        }

        .timer-btn.running {
            background: #F44336;
        }

        .timer-btn.running:hover {
            background: #da190b;
        }

        /* Notas del QSO */
        .qso-notes {
            width: 100%;
            padding: 10px 12px;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.2s ease;
            line-height: 1.4;
        }

        .qso-notes:focus {
            outline: none;
            border-color: #4CAF50;
        }

        /* Formulario de añadir indicativo */
        .add-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .input-field {
            padding: 12px;
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .input-field::placeholder {
            color: #666;
        }

        .add-callsign-btn {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            font-weight: 600;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 10px;
            -webkit-tap-highlight-color: rgba(33, 150, 243, 0.1);
            touch-action: manipulation;
        }

        .add-callsign-btn:hover {
            background: #0b7dda;
        }

        .add-callsign-btn:active {
            transform: scale(0.97);
        }

        .export-link {
            font-size: 13px;
            color: #888;
            text-decoration: none;
            transition: color 0.2s ease;
            cursor: pointer;
        }

        .export-link:hover {
            color: #4CAF50;
            text-decoration: underline;
        }

        /* Lista de indicativos */
        .callsign-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .callsign-item {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .callsign-item:hover {
            border-color: #4CAF50;
            background: #222;
        }

        .drag-handle {
            cursor: grab;
            color: #555;
            font-size: 16px;
            user-select: none;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .callsign-item.dragging {
            opacity: 0.5;
            border-color: #4CAF50;
        }

        .callsign-info {
            flex: 1;
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .callsign-text {
            font-size: 16px;
            font-weight: bold;
            color: #4CAF50;
            font-family: 'Courier New', monospace;
        }

        .callsign-name {
            font-size: 13px;
            color: #888;
        }

        .callsign-actions {
            display: flex;
            gap: 4px;
        }

        .action-btn {
            padding: 6px 10px;
            font-size: 14px;
            background: transparent;
            color: #888;
            border: 1px solid #333;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            -webkit-tap-highlight-color: rgba(255, 255, 255, 0.1);
            touch-action: manipulation;
        }

        .action-btn:hover {
            background: #333;
        }

        .action-btn.edit-btn:hover {
            border-color: #FFC107;
            color: #FFC107;
        }

        .action-btn.qrz-btn:hover {
            border-color: #2196F3;
            color: #2196F3;
        }

        .action-btn.delete-btn:hover {
            border-color: #F44336;
            color: #F44336;
        }

        .empty-state {
            text-align: center;
            padding: 30px 15px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 36px;
            margin-bottom: 8px;
            opacity: 0.5;
        }

        /* Modal de edición */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            max-width: 400px;
            width: 100%;
        }

        .modal-title {
            font-size: 18px;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-btn.save {
            background: #4CAF50;
            color: white;
        }

        .modal-btn.save:hover {
            background: #45a049;
        }

        .modal-btn.cancel {
            background: #666;
            color: white;
        }

        .modal-btn.cancel:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="container qso-container">
        <?php renderNavMenu('qso.php'); ?>

        <!-- Cronómetro -->
        <div class="qso-section">
            <div class="section-title">Cronómetro</div>
            <div class="timer-container">
                <div class="timer-display" id="timerDisplay">00:00</div>
                <button class="timer-btn" id="timerBtn" onclick="toggleTimer()">▶️ Iniciar</button>
            </div>
        </div>

        <!-- Notas del QSO -->
        <div class="qso-section">
            <div class="section-title">Notas</div>
            <textarea id="qsoNotes" class="qso-notes" placeholder="Notas del QSO..." rows="3"></textarea>
        </div>

        <!-- Lista de indicativos -->
        <div class="qso-section">
            <div class="section-title">Indicativos</div>
            <button class="add-callsign-btn" onclick="openAddModal()">➕ Añadir Indicativo</button>
            <div id="callsignList" class="callsign-list">
                <div class="empty-state">
                    <div class="empty-state-icon">📡</div>
                    <div style="font-size: 14px; color: #777;">No hay indicativos</div>
                </div>
            </div>
        </div>

        <!-- Enlace de exportación -->
        <div class="qso-section" style="text-align: center; margin-top: 20px;">
            <a href="#" class="export-link" onclick="exportQSOData(); return false;">💾 Exportar datos a archivo</a>
        </div>
    </div>

    <!-- Modal de añadir -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">➕ Añadir Indicativo</div>
            <div class="add-form">
                <input type="text" id="addCallsignInput" class="input-field" placeholder="Indicativo (ej: EA4XYZ)" maxlength="15">
                <input type="text" id="addNameInput" class="input-field" placeholder="Nombre (opcional)" maxlength="50">
            </div>
            <div class="modal-buttons">
                <button class="modal-btn save" onclick="addCallsign()">➕ Añadir</button>
                <button class="modal-btn cancel" onclick="closeAddModal()">❌ Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal de edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">✏️ Editar Indicativo</div>
            <div class="add-form">
                <input type="text" id="editCallsignInput" class="input-field" placeholder="Indicativo">
                <input type="text" id="editNameInput" class="input-field" placeholder="Nombre">
            </div>
            <div class="modal-buttons">
                <button class="modal-btn save" onclick="saveEdit()">💾 Guardar</button>
                <button class="modal-btn cancel" onclick="closeEditModal()">❌ Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        // ========== CRONÓMETRO ==========
        let timerInterval = null;
        let timerSeconds = 0;
        let isRunning = false;

        function toggleTimer() {
            const btn = document.getElementById('timerBtn');

            if (isRunning) {
                // Detener y resetear
                clearInterval(timerInterval);
                timerSeconds = 0;
                isRunning = false;
                updateTimerDisplay();
                btn.textContent = '▶️ Iniciar';
                btn.classList.remove('running');
            } else {
                // Iniciar
                isRunning = true;
                timerInterval = setInterval(() => {
                    timerSeconds++;
                    updateTimerDisplay();
                    saveTimerState();
                }, 1000);
                btn.textContent = '⏹️ Detener y Reiniciar';
                btn.classList.add('running');
            }

            saveTimerState();
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timerSeconds / 60);
            const seconds = timerSeconds % 60;
            const display = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            document.getElementById('timerDisplay').textContent = display;
        }

        function saveTimerState() {
            localStorage.setItem('qsoTimerState', JSON.stringify({
                seconds: timerSeconds,
                isRunning: isRunning,
                timestamp: Date.now()
            }));
        }

        function loadTimerState() {
            const saved = localStorage.getItem('qsoTimerState');
            if (saved) {
                const state = JSON.parse(saved);
                if (state.isRunning) {
                    // Calcular tiempo transcurrido desde que se guardó
                    const elapsed = Math.floor((Date.now() - state.timestamp) / 1000);
                    timerSeconds = state.seconds + elapsed;
                    updateTimerDisplay();
                    toggleTimer(); // Iniciar el cronómetro
                } else {
                    timerSeconds = state.seconds;
                    updateTimerDisplay();
                }
            }
        }

        // ========== NOTAS ==========
        const notesTextarea = document.getElementById('qsoNotes');

        notesTextarea.addEventListener('input', () => {
            localStorage.setItem('qsoNotes', notesTextarea.value);
        });

        function loadNotes() {
            const saved = localStorage.getItem('qsoNotes');
            if (saved) {
                notesTextarea.value = saved;
            }
        }

        // ========== LISTA DE INDICATIVOS ==========
        let callsigns = [];
        let draggedIndex = null;
        let editingIndex = null;

        function openAddModal() {
            document.getElementById('addCallsignInput').value = '';
            document.getElementById('addNameInput').value = '';
            document.getElementById('addModal').classList.add('show');
            // Enfocar el campo de indicativo después de que el modal se muestre
            setTimeout(() => {
                document.getElementById('addCallsignInput').focus();
            }, 100);
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }

        function addCallsign() {
            const callsignInput = document.getElementById('addCallsignInput');
            const nameInput = document.getElementById('addNameInput');

            const callsign = callsignInput.value.trim().toUpperCase();
            const name = nameInput.value.trim();

            if (!callsign) {
                alert('Por favor, introduce un indicativo');
                return;
            }

            callsigns.push({
                callsign: callsign,
                name: name
            });

            saveCallsigns();
            renderCallsigns();
            closeAddModal();
        }

        function deleteCallsign(index) {
            if (confirm(`¿Eliminar ${callsigns[index].callsign} de la lista?`)) {
                callsigns.splice(index, 1);
                saveCallsigns();
                renderCallsigns();
            }
        }

        function openEditModal(index) {
            editingIndex = index;
            const callsign = callsigns[index];

            document.getElementById('editCallsignInput').value = callsign.callsign;
            document.getElementById('editNameInput').value = callsign.name;
            document.getElementById('editModal').classList.add('show');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
            editingIndex = null;
        }

        function saveEdit() {
            if (editingIndex === null) return;

            const callsign = document.getElementById('editCallsignInput').value.trim().toUpperCase();
            const name = document.getElementById('editNameInput').value.trim();

            if (!callsign) {
                alert('Por favor, introduce un indicativo');
                return;
            }

            callsigns[editingIndex] = {
                callsign: callsign,
                name: name
            };

            saveCallsigns();
            renderCallsigns();
            closeEditModal();
        }

        function openQRZ(callsign) {
            window.open(`https://www.qrz.com/db/${callsign}`, '_blank');
        }

        function renderCallsigns() {
            const container = document.getElementById('callsignList');

            if (callsigns.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📡</div>
                        <div style="font-size: 14px; color: #777;">No hay indicativos</div>
                    </div>
                `;
                return;
            }

            container.innerHTML = callsigns.map((item, index) => `
                <div class="callsign-item" draggable="true" data-index="${index}">
                    <div class="drag-handle">☰</div>
                    <div class="callsign-info">
                        <div class="callsign-text">${item.callsign}</div>
                        ${item.name ? `<div class="callsign-name">${item.name}</div>` : ''}
                    </div>
                    <div class="callsign-actions">
                        <button class="action-btn edit-btn" onclick="openEditModal(${index})" title="Editar">✏️</button>
                        <button class="action-btn qrz-btn" onclick="openQRZ('${item.callsign}')" title="Abrir en QRZ.com">🔍</button>
                        <button class="action-btn delete-btn" onclick="deleteCallsign(${index})" title="Eliminar">🗑️</button>
                    </div>
                </div>
            `).join('');

            // Añadir eventos de drag and drop
            setupDragAndDrop();
        }

        function setupDragAndDrop() {
            const items = document.querySelectorAll('.callsign-item');

            items.forEach((item, index) => {
                item.addEventListener('dragstart', (e) => {
                    draggedIndex = index;
                    item.classList.add('dragging');
                });

                item.addEventListener('dragend', (e) => {
                    item.classList.remove('dragging');
                    draggedIndex = null;
                });

                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                });

                item.addEventListener('drop', (e) => {
                    e.preventDefault();
                    const dropIndex = parseInt(item.dataset.index);

                    if (draggedIndex !== null && draggedIndex !== dropIndex) {
                        // Reordenar array
                        const draggedItem = callsigns[draggedIndex];
                        callsigns.splice(draggedIndex, 1);
                        callsigns.splice(dropIndex, 0, draggedItem);

                        saveCallsigns();
                        renderCallsigns();
                    }
                });
            });
        }

        function saveCallsigns() {
            localStorage.setItem('qsoCallsigns', JSON.stringify(callsigns));
        }

        function loadCallsigns() {
            const saved = localStorage.getItem('qsoCallsigns');
            if (saved) {
                callsigns = JSON.parse(saved);
                renderCallsigns();
            }
        }

        // Permitir añadir con Enter en el modal de añadir
        document.getElementById('addCallsignInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                addCallsign();
            }
        });

        document.getElementById('addNameInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                addCallsign();
            }
        });

        // Cerrar modales al hacer click fuera
        document.getElementById('addModal').addEventListener('click', (e) => {
            if (e.target.id === 'addModal') {
                closeAddModal();
            }
        });

        document.getElementById('editModal').addEventListener('click', (e) => {
            if (e.target.id === 'editModal') {
                closeEditModal();
            }
        });

        // ========== EXPORTACIÓN ==========
        function exportQSOData() {
            // Obtener fecha y hora actual
            const now = new Date();
            const fecha = now.toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            const hora = now.toLocaleTimeString('es-ES');

            // Obtener notas
            const notas = document.getElementById('qsoNotes').value || '(Sin notas)';

            // Obtener tiempo del cronómetro
            const timerMinutes = Math.floor(timerSeconds / 60);
            const timerSecondsRem = timerSeconds % 60;
            const tiempoQSO = `${String(timerMinutes).padStart(2, '0')}:${String(timerSecondsRem).padStart(2, '0')}`;

            // Construir contenido del archivo
            let contenido = '';
            contenido += '================================================\n';
            contenido += '           REGISTRO DE QSO - RADIOTOOLS\n';
            contenido += '================================================\n\n';
            contenido += `Fecha: ${fecha}\n`;
            contenido += `Hora: ${hora}\n`;

            contenido += '------------------------------------------------\n';
            contenido += 'NOTAS DEL QSO\n';
            contenido += '------------------------------------------------\n';
            contenido += notas + '\n\n';

            if (callsigns.length > 0) {
                contenido += '------------------------------------------------\n';
                contenido += 'INDICATIVOS CONTACTADOS\n';
                contenido += '------------------------------------------------\n\n';

                callsigns.forEach((item, index) => {
                    contenido += `${index + 1}. ${item.callsign}`;
                    if (item.name) {
                        contenido += ` - ${item.name}`;
                    }
                    contenido += '\n';
                });
            } else {
                contenido += '------------------------------------------------\n';
                contenido += 'INDICATIVOS CONTACTADOS\n';
                contenido += '------------------------------------------------\n';
                contenido += '(No hay indicativos registrados)\n';
            }

            contenido += '\n================================================\n';
            contenido += '73! - Buena propagación y buenos contactos DX\n';
            contenido += '================================================\n';

            // Crear y descargar archivo
            const blob = new Blob([contenido], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            // Nombre de archivo con fecha
            const nombreArchivo = `QSO_${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}_${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}.txt`;

            link.href = url;
            link.download = nombreArchivo;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        // ========== INICIALIZACIÓN ==========
        loadTimerState();
        loadNotes();
        loadCallsigns();
    </script>
</body>
</html>
