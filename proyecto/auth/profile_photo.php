<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../config/sql.php');

function ensure_profile_photo_column($conn) {
    $result = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
    if ($result && $result->num_rows > 0) {
        return true;
    }

    return $conn->query("ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL") === true;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Selecciona una imagen válida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'La imagen no debe pesar más de 2 MB.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($extension, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Formato inválido. Usa JPG, PNG o WEBP.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$targetDir = '../media/pfp/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$userId = (int)$_SESSION['user_id'];
$fileName = 'user_' . $userId . '_' . time() . '.' . $extension;
$targetFile = $targetDir . $fileName;
$publicPath = 'media/pfp/' . $fileName;

if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile)) {
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar la imagen.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!ensure_profile_photo_column($conn)) {
    echo json_encode(['success' => false, 'error' => 'No se pudo preparar la base de datos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
$stmt->bind_param("si", $publicPath, $userId);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'No se pudo actualizar tu perfil.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->close();
$conn->close();

$_SESSION['user_foto_perfil'] = $publicPath;

echo json_encode([
    'success' => true,
    'foto_perfil' => $publicPath
], JSON_UNESCAPED_UNICODE);
