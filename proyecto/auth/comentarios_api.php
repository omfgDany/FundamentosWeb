<?php
// auth/comentarios_api.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../config/sql.php');

$method = $_SERVER['REQUEST_METHOD'];

// CASO GET: Obtener comentarios de una receta
if ($method === 'GET') {
    $publicacion_id = isset($_GET['publicacion_id']) ? (int)$_GET['publicacion_id'] : 0;
    
    $sql = "SELECT c.comentario, c.fecha_comentario, u.nombre AS autor 
            FROM comentarios c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.publicacion_id = ? 
            ORDER BY c.fecha_comentario DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comentarios = [];
    while ($row = $result->fetch_assoc()) {
        $comentarios[] = [
            'autor' => $row['autor'],
            'comentario' => $row['comentario'],
            'fecha' => date('d/m/Y H:i', strtotime($row['fecha_comentario']))
        ];
    }
    echo json_encode($comentarios, JSON_UNESCAPED_UNICODE);
    exit;
}

// CASO POST: Guardar un nuevo comentario
if ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Inicia sesión primero.']);
        exit;
    }
    
    $body = json_decode(file_get_contents('php://input'), true);
    $publicacion_id = (int)($body['publicacion_id'] ?? 0);
    $comentario = trim($body['comentario'] ?? '');
    
    if (!$comentario) {
        echo json_encode(['success' => false, 'error' => 'El comentario no puede estar vacío.']);
        exit;
    }
    
    $sql = "INSERT INTO comentarios (publicacion_id, usuario_id, comentario) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $publicacion_id, $_SESSION['user_id'], $comentario);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo guardar el comentario.']);
    }
    exit;
}