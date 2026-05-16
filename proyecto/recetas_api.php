<?php
header('Content-Type: application/json');
require_once('config/sql.php');

$sql = "SELECT r.id, r.titulo, r.descripcion, r.fecha_publicacion, r.imagen, u.nombre AS autor, u.id AS usuario_id, u.rol
FROM recetas r
JOIN usuarios u ON r.usuario_id = u.id
ORDER BY r.fecha_publicacion DESC";

$result = $conn->query($sql);
$recetas = [];

while ($row = $result->fetch_assoc()) {
    $fecha = strtotime($row['fecha_publicacion']);
    $recetas[] = [
        'id' => $row['id'],
        'titulo' => $row['titulo'],
        'descripcion' => $row['descripcion'],
        'anio' => (int)date('Y', $fecha),
        'mes' => (int)date('n', $fecha),
        'dia' => (int)date('j', $fecha),
        'autor' => $row['autor'],
        'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($row['autor']),
        'categoria' => 'General', // Cambia si tienes categorías
        'dificultad' => 'Media',  // Cambia si tienes dificultad
        'likes' => rand(5, 50),   // Simulación, reemplaza si tienes likes reales
        'comentarios' => rand(0, 10), // Simulación, reemplaza si tienes comentarios reales
        'imagen' => !empty($row['imagen'])
            ? $row['imagen']
            : 'https://source.unsplash.com/400x300/?food,recipe',
            ];
}

$conn->close();
echo json_encode($recetas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);