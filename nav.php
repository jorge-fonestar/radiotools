<?php
// nav.php - Menú de navegación común para todas las páginas

function renderNavMenu($currentPage) {
    $pages = [
        'index.php' => ['🏠 Inicio', 'Página principal del sistema'],
        'solar.php' => ['📡 Monitor Solar', 'Condiciones solares en tiempo real'],
        'qso.php' => ['📝 Gestor QSO', 'Gestor de contactos y notas de QSO'],
        'links.php' => ['🔗 Enlaces de Interés', 'Recursos y enlaces útiles'],
        'propagacion.php' => ['📻 Calculador Propagación', 'Predicción de propagación HF'],
        'espectro-visual.php' => ['🌈 Espectro Radioeléctrico', 'Visualizador de espectro radioeléctrico'],
        'waterfall.php' => ['🌊 Decodificador Waterfall', 'Convierte imágenes de waterfall SDR a audio'],
    ];

    $external_links = [
        'https://docs.google.com/document/d/1r2CloQgovSGbe9PjPuUlWYmzm1d-khQ1XCfvc8nwFkI/edit?usp=sharing' => ['🔧 DIY Radio', 'Proyectos y construcciones DIY']
    ];
    
    // Obtener el nombre de la página actual para mostrar en la barra
    $currentPageName = isset($pages[$currentPage]) ? $pages[$currentPage][0] : '🌐 Sistema HF';
    
    echo '<div class="hamburger-menu">';
    echo '<div class="menu-bar" onclick="toggleMenu()">';
    echo '<div class="hamburger-icon">';
    echo '<span></span>';
    echo '<span></span>';
    echo '<span></span>';
    echo '</div>';
    echo '<div class="current-page">' . $currentPageName . '</div>';
    echo '<div class="menu-arrow">▼</div>';
    echo '</div>';
    
    echo '<div class="menu-dropdown" id="menuDropdown">';
    foreach ($pages as $file => $info) {
        $isActive = ($file === $currentPage) ? 'active' : '';
        echo '<a href="' . $file . '" class="menu-item ' . $isActive . '">';
        echo '<span class="menu-text">' . $info[0] . '</span>';
        echo '</a>';
    }
    // Añadir enlaces externos
    foreach ($external_links as $url => $info) {
        echo '<a href="' . $url . '" target="_blank" class="menu-item">';
        echo '<span class="menu-text">' . $info[0] . '</span>';
        echo '</a>';
    }
    echo '</div>';
    echo '</div>';
    
    // JavaScript para manejar el menú desplegable
    echo '<script>';
    echo 'function toggleMenu() {';
    echo '  const dropdown = document.getElementById("menuDropdown");';
    echo '  const arrow = document.querySelector(".menu-arrow");';
    echo '  dropdown.classList.toggle("show");';
    echo '  arrow.style.transform = dropdown.classList.contains("show") ? "rotate(180deg)" : "rotate(0deg)";';
    echo '}';
    echo '';
    echo '// Cerrar menú al hacer click fuera';
    echo 'document.addEventListener("click", function(event) {';
    echo '  const menu = document.querySelector(".hamburger-menu");';
    echo '  if (!menu.contains(event.target)) {';
    echo '    const dropdown = document.getElementById("menuDropdown");';
    echo '    const arrow = document.querySelector(".menu-arrow");';
    echo '    dropdown.classList.remove("show");';
    echo '    arrow.style.transform = "rotate(0deg)";';
    echo '  }';
    echo '});';
    echo '</script>';
}
?>