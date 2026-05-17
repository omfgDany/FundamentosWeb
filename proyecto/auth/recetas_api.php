<?php
// recetas_api.php
header('Content-Type: application/json; charset=utf-8');
require_once('../config/sql.php');

// Comprobamos si el frontend está pidiendo una receta específica por ID
$id_receta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT 
            r.id, 
            r.titulo, 
            r.categoria,
            r.descripcion, 
            r.ingredientes,
            r.preparacion,
            r.fecha_creacion, 
            r.imagen, 
            u.nombre AS autor, 
            u.id AS usuario_id,
            (SELECT COUNT(*) FROM likes l WHERE l.publicacion_id = r.id) AS total_likes,
            (SELECT COUNT(*) FROM comentarios c WHERE c.publicacion_id = r.id) AS total_comentarios
        FROM recetas r
        JOIN usuarios u ON r.usuario_id = u.id";

// Si se envió un ID, filtramos la consulta SQL
if ($id_receta > 0) {
    $sql .= " WHERE r.id = " . $id_receta;
} else {
    $sql .= " ORDER BY r.fecha_creacion DESC";
}

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => 'Fallo en la consulta SQL: ' . $conn->error]);
    exit;
}

$recetas = [];

while ($row = $result->fetch_assoc()) {
    $fecha = strtotime($row['fecha_creacion']);
    
    $recetas[] = [
        'id'          => (int)$row['id'],
        'titulo'      => $row['titulo'],
        'descripcion' => $row['descripcion'],
        'ingredientes'=> $row['ingredientes'] ?? '', // Enviamos los ingredientes
        'preparacion' => $row['preparacion'] ?? '',  // Enviamos la preparación
        'anio'        => (int)date('Y', $fecha),
        'mes'         => (int)date('n', $fecha),
        'dia'         => (int)date('j', $fecha),
        'autor'       => $row['autor'],
        'avatar'      => 'https://ui-avatars.com/api/?name=' . urlencode($row['autor']),
        'categoria'   => !empty($row['categoria']) ? $row['categoria'] : 'General',
        'likes'       => (int)$row['total_likes'],       
        'comentarios' => (int)$row['total_comentarios'], 
        'imagen'      => !empty($row['imagen']) ? $row['imagen'] : 'media/recipes/default.jpg'
    ];
}

$conn->close();

// Si se pidió una sola receta, devolvemos el objeto directo en lugar de un array
if ($id_receta > 0 && count($recetas) > 0) {
    echo json_encode($recetas[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode($recetas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}