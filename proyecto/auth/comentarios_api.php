<?php
header('Content-Type: application/json; charset=utf-8');
require_once('config/sql.php');

if (!isset($_GET['receta_id'])) {
    echo json_encode([
        'error' => 'Falta receta_id'
    ]);
    exit;
}

$receta_id = intval($_GET['receta_id']);

$sql = "SELECT 
            c.id,
            c.comentario,
            c.fecha_creacion,
            u.nombre AS autor
        FROM comentarios c
        JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.publicacion_id = ?
        ORDER BY c.fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $receta_id);
$stmt->execute();

$result = $stmt->get_result();

$comentarios = [];

while ($row = $result->fetch_assoc()) {
    $comentarios[] = [
        'id' => (int)$row['id'],
        'autor' => $row['autor'],
        'comentario' => $row['comentario'],
        'fecha' => $row['fecha_creacion']
    ];
}

echo json_encode($comentarios, JSON_UNESCAPED_UNICODE);