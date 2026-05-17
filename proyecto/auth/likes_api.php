<?php
// auth/likes_api.php
session_start();
header('Content-Type: application/json');
require_once('../config/sql.php');

$method = $_SERVER['REQUEST_METHOD'];
$usuario_id = $_SESSION['user_id'] ?? null;

// CASO GET: Saber si el usuario actual ya le dio like a esta receta
if ($method === 'GET') {
    $publicacion_id = isset($_GET['publicacion_id']) ? (int)$_GET['publicacion_id'] : 0;
    $hasLiked = false;
    
    if ($usuario_id) {
        $stmt = $conn->prepare("SELECT 1 FROM likes WHERE publicacion_id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $hasLiked = true;
        $stmt->close();
    }
    echo json_encode(['hasLiked' => $hasLiked]);
    exit;
}

// CASO POST: Dar o Quitar Like (Toggle)
if ($method === 'POST') {
    if (!$usuario_id) {
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        exit;
    }
    
    $body = json_decode(file_get_contents('php://input'), true);
    $publicacion_id = (int)($body['publicacion_id'] ?? 0);
    
    // Verificamos si ya existe el registro
    $stmt = $conn->prepare("SELECT 1 FROM likes WHERE publicacion_id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    $ya_existe = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    
    if ($ya_existe) {
        // Si ya existía, lo quitamos (Un-like)
        $stmt = $conn->prepare("DELETE FROM likes WHERE publicacion_id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        $hasLiked = false;
    } else {
        // Si no existía, lo agregamos (Like)
        $stmt = $conn->prepare("INSERT INTO likes (publicacion_id, usuario_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        $hasLiked = true;
    }
    $stmt->close();
    
    // Contamos el total actualizado de likes de la receta para devolvérselo al frontend
    $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE publicacion_id = ?");
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $res_count = $stmt->get_result()->fetch_row();
    $total_likes = $res_count[0];
    
    echo json_encode([
        'success' => true,
        'hasLiked' => $hasLiked,
        'total_likes' => $total_likes
    ]);
    exit;
}