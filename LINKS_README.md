# 📝 Guía de Edición de Enlaces

Este archivo explica cómo editar fácilmente los enlaces de interés en el archivo `links.php`.

## Estructura del Código

El archivo `links.php` contiene un array simple `$links_config` organizado por secciones.

### Formato de una Sección

```php
[
    'title' => '📍 Título de la Sección',
    'links' => [
        ['icon' => '🔗', 'text' => 'Nombre del enlace', 'url' => 'https://ejemplo.com'],
        ['icon' => '📡', 'text' => 'Otro enlace', 'url' => 'https://otro.com'],
    ]
]
```

## Cómo Editar

### Añadir un nuevo enlace a una sección existente

Simplemente añade una línea dentro del array `'links'`:

```php
[
    'title' => '📍 Mapas y Ubicación',
    'links' => [
        ['icon' => '🗺️', 'text' => 'Mapa topográfico', 'url' => 'https://...'],
        // NUEVO ENLACE AQUÍ:
        ['icon' => '🌍', 'text' => 'Google Maps', 'url' => 'https://maps.google.com'],
    ]
]
```

### Crear una nueva sección

Añade un nuevo bloque al array `$links_config`:

```php
$links_config = [
    // ... secciones existentes ...

    // NUEVA SECCIÓN:
    [
        'title' => '🎓 Educación',
        'links' => [
            ['icon' => '📚', 'text' => 'Curso de radioafición', 'url' => 'https://...'],
            ['icon' => '🎥', 'text' => 'Videos tutoriales', 'url' => 'https://...'],
        ]
    ],
];
```

### Cambiar un icono

Simplemente modifica el campo `'icon'`. Puedes usar cualquier emoji:

```php
['icon' => '🚀', 'text' => 'Mi enlace', 'url' => 'https://...']
```

Emojis útiles: 📡 🛰️ 📻 🗺️ 🌍 📚 🎥 🔧 💻 📊 🌐 🔗 ⚡ 🎯

### Eliminar un enlace

Simplemente borra o comenta la línea correspondiente:

```php
// ['icon' => '🔗', 'text' => 'Enlace viejo', 'url' => 'https://...'],
```

### Reordenar enlaces

Corta y pega las líneas en el orden que prefieras. El primer enlace del array aparecerá primero en el modal.

## Ejemplo Completo

```php
$links_config = [
    [
        'title' => '📍 Mapas',
        'links' => [
            ['icon' => '🗺️', 'text' => 'Mapa topográfico', 'url' => 'https://topographic-map.com'],
            ['icon' => '🌍', 'text' => 'Google Maps', 'url' => 'https://maps.google.com'],
        ]
    ],
    [
        'title' => '📻 Radio',
        'links' => [
            ['icon' => '📡', 'text' => 'RTL-SDR', 'url' => 'https://rtl-sdr.com'],
        ]
    ],
];
```

## Consejos

1. **Mantén el formato**: Respeta las comas, corchetes y comillas
2. **Prueba después de editar**: Abre `index.php` y verifica que el modal se abra correctamente
3. **Usa emojis consistentes**: Ayudan a identificar visualmente el tipo de enlace
4. **URLs completas**: Siempre incluye `https://` o `http://`
5. **Escapa caracteres especiales**: Si la URL tiene `&`, está bien, PHP lo maneja automáticamente

## Errores Comunes

❌ **Olvidar la coma entre enlaces:**
```php
['icon' => '🔗', 'text' => 'Link 1', 'url' => 'https://...']
['icon' => '🔗', 'text' => 'Link 2', 'url' => 'https://...']  // ¡Falta coma arriba!
```

✅ **Correcto:**
```php
['icon' => '🔗', 'text' => 'Link 1', 'url' => 'https://...'],
['icon' => '🔗', 'text' => 'Link 2', 'url' => 'https://...'],
```

❌ **Mezclar comillas simples y dobles:**
```php
['icon' => "🔗", 'text' => 'Link', 'url' => 'https://...']  // Inconsistente
```

✅ **Correcto (usa siempre comillas simples):**
```php
['icon' => '🔗', 'text' => 'Link', 'url' => 'https://...']
```
