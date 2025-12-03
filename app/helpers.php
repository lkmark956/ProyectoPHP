<?php
/**
 * Funciones auxiliares para el sitio
 */

/**
 * Obtiene un emoji único para cada categoría basándose en su nombre o ID
 * 
 * @param array $category Array con los datos de la categoría
 * @return string Emoji correspondiente
 */
function getCategoryEmoji($category) {
    // Mapeo de nombres de categoría a emojis
    $emojiMap = [
        'tecnología' => '💻',
        'tecnologia' => '💻',
        'tech' => '💻',
        'programación' => '👨‍💻',
        'programacion' => '👨‍💻',
        'code' => '👨‍💻',
        'diseño' => '🎨',
        'diseno' => '🎨',
        'design' => '🎨',
        'viajes' => '✈️',
        'travel' => '✈️',
        'comida' => '🍽️',
        'food' => '🍽️',
        'deportes' => '⚽',
        'sports' => '⚽',
        'música' => '🎵',
        'musica' => '🎵',
        'music' => '🎵',
        'cine' => '🎬',
        'movies' => '🎬',
        'libros' => '📚',
        'books' => '📚',
        'ciencia' => '🔬',
        'science' => '🔬',
        'salud' => '🏥',
        'health' => '🏥',
        'negocios' => '💼',
        'business' => '💼',
        'finanzas' => '💰',
        'finance' => '💰',
        'educación' => '🎓',
        'educacion' => '🎓',
        'education' => '🎓',
        'moda' => '👗',
        'fashion' => '👗',
        'gaming' => '🎮',
        'juegos' => '🎮',
        'fotografía' => '📷',
        'fotografia' => '📷',
        'photography' => '📷',
        'noticias' => '📰',
        'news' => '📰',
        'política' => '🏛️',
        'politica' => '🏛️',
        'politics' => '🏛️',
        'naturaleza' => '🌿',
        'nature' => '🌿',
        'arte' => '🖼️',
        'art' => '🖼️',
        'historia' => '📜',
        'history' => '📜',
        'cocina' => '👨‍🍳',
        'cooking' => '👨‍🍳',
        'automoción' => '🚗',
        'automocion' => '🚗',
        'cars' => '🚗',
        'hogar' => '🏠',
        'home' => '🏠',
        'animales' => '🐾',
        'animals' => '🐾',
        'pets' => '🐾'
    ];
    
    // Buscar emoji por nombre de categoría
    $categoryName = strtolower(trim($category['name'] ?? ''));
    
    foreach ($emojiMap as $keyword => $emoji) {
        if (stripos($categoryName, $keyword) !== false) {
            return $emoji;
        }
    }
    
    // Si no hay coincidencia, usar emojis basados en el ID
    $emojis = ['📁', '🗂️', '📋', '📌', '🏷️', '📂', '📎', '🔖', '📝', '📄'];
    $id = $category['id'] ?? 0;
    
    return $emojis[$id % count($emojis)];
}

/**
 * Obtiene el emoji para mostrar en la página de categoría individual
 * 
 * @param array $category Array con los datos de la categoría
 * @return string Emoji correspondiente (más grande para headers)
 */
function getCategoryHeaderEmoji($category) {
    return getCategoryEmoji($category);
}
